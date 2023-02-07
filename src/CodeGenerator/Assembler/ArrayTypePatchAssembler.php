<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Phpro\SoapClient\CodeGenerator\Context;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;

class ArrayTypePatchAssembler implements AssemblerInterface
{

    private $options;

    public function __construct(ArrayTypePatchAssemblerOptions $options = null)
    {
        $this->options = $options ?? new ArrayTypePatchAssemblerOptions();
    }

    /**
     * @param Context\ContextInterface $context
     *
     * @return bool
     */
    public function canAssemble(Context\ContextInterface $context): bool
    {
        return $context instanceof Context\TypeContext || $context instanceof Context\PropertyContext;
    }

    public function assemble(Context\ContextInterface $context)
    {
        if ($context instanceof Context\TypeContext) {
            $this->assembleType($context);
        }

        if ($context instanceof Context\PropertyContext) {
            $this->assembleProperty($context);
        }
    }

    public function assembleType(Context\TypeContext $context)
    {
        $class = $context->getClass();
        $properties = $context->getType()->getProperties();
        $firstProperty = count($properties) ? current($properties) : null;

        try {
            $this->implementAsArrayOfMethod($class, $firstProperty);

            if ($this->options->useArrayAccessPatch()) {
                $this->applyAsArrayOfPath($class, $firstProperty, 'offsetExists');
                $this->applyAsArrayOfPath($class, $firstProperty, 'offsetGet');
                $this->applyAsArrayOfPath($class, $firstProperty, 'offsetSet');
                $this->applyAsArrayOfPath($class, $firstProperty, 'offsetUnset');
            }

            if ($this->options->useCountablePatch()) {
                $this->applyAsArrayOfPath($class, $firstProperty, 'count');
            }

            if ($this->options->useIteratorPatch()) {
                $this->applyAsArrayOfPath($class, $firstProperty, 'getIterator');
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    public function assembleProperty(Context\PropertyContext $context)
    {
        $class = $context->getClass();
        $property = $context->getProperty();
        $type = $context->getType();
        $properties = $type->getProperties();
        $firstProperty = count($properties) ? current($properties) : null;

        if (!$firstProperty || $firstProperty !== $property) {
            return;
        }

        try {
            $this->applyPropertyTypePatch($class, $property);

            if ($this->options->useGetterPatch()) {
                $getterAssembler = new GetterAssembler($this->options->getGetterOptions());
                if ($getterAssembler->canAssemble($context)) {
                    $getterAssembler->assemble($context);
                    $this->applyGetterPatch($class, $property);
                }
            }

            if (!$this->options->useFluentSetterPatch()
                && !$this->options->useImmutableSetterPatch()) {
                $setterAssembler = new SetterAssembler($this->options->getSetterOptions());
                if ($setterAssembler->canAssemble($context)) {
                    $setterAssembler->assemble($context);
                    $this->applySetterPatch(
                        $class,
                        $property,
                        'set',
                        $this->options->getSetterOptions() ?? new SetterAssemblerOptions()
                    );
                }
            }

            if ($this->options->useFluentSetterPatch()) {
                $fluentSetterAssembler = new FluentSetterAssembler($this->options->getFluentSetterOptions());
                if ($fluentSetterAssembler->canAssemble($context)) {
                    $fluentSetterAssembler->assemble($context);
                    $this->applySetterPatch(
                        $class,
                        $property,
                        'set',
                        $this->options->getFluentSetterOptions() ?? new FluentSetterAssemblerOptions()
                    );
                }
            }

            if ($this->options->useImmutableSetterPatch()) {
                $immutableSetterAssembler = new ImmutableSetterAssembler(
                    $this->options->getImmutableSetterOptions()
                );
                if ($immutableSetterAssembler->canAssemble($context)) {
                    $immutableSetterAssembler->assemble($context);
                    $this->applySetterPatch(
                        $class,
                        $property,
                        'with',
                        $this->options->getImmutableSetterOptions() ?? new ImmutableSetterAssemblerOptions()
                    );
                }
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    /**
     * @param Generator\ClassGenerator $class
     * @param Property $property
     * @param string $prefix
     * @param SetterAssemblerOptions|FluentSetterAssemblerOptions|ImmutableSetterAssemblerOptions $options
     * @return void
     */
    private function applySetterPatch(Generator\ClassGenerator $class, Property $property, string $prefix, $options)
    {
        $methodName = Normalizer::generatePropertyMethod($prefix, $property->getName());

        if (!$class->hasMethod($methodName)) {
            return;
        }

        $method = $class->getMethod($methodName);
        $class->removeMethod($method->getName());

        $lines = [
            $this->getSetterPatchCode($class, $property),
            '',
            $method->getBody()
        ];

        $body = implode($class::LINE_FEED, $lines);
        $method->setBody($body);

        if ($options && $options->useDocBlocks()) {
            $docBlock = $method->getDocBlock();
            $method->setDocBlock(
                Generator\DocBlockGenerator::fromArray($this->replaceDocblockParam($docBlock, $property))
            );
        }

        $method->setParameter(['name' => $property->getName()]);

        $class->addMethodFromGenerator($method);
    }

    private function replaceDocblockParam(Generator\DocBlockGenerator $docBlock, Property $property): array
    {
        $newDocBlock = [];

        $newDocBlock['shortDescription'] = $docBlock->getShortDescription();
        $newDocBlock['longDescription'] = $docBlock->getLongDescription();

        $tags = $docBlock->getTags();
        foreach ($tags as $tag) {
            if ($tag->getName() != 'param') {
                $newDocBlock['tags'][] = $tag;
            }
        }

        $newDocBlock['tags'][] = new ParamTag(
            $property->getName(),
            [$property->getType(), sprintf('%s[]', $property->getType())]
        );

        return $newDocBlock;
    }

    private function implementAsArrayOfMethod(Generator\ClassGenerator $class, Property $property)
    {
        $methodName = $this->getAsArrayOfMethodName($property);
        $class->removeMethod($methodName);

        $methodGenerator = new Generator\MethodGenerator($methodName);
        $methodGenerator->setVisibility(Generator\MethodGenerator::VISIBILITY_PRIVATE);

        $lines = [
            sprintf('if (!is_array($this->%s)) {', $property->getName()),
            "\t" . '/** @noinspection PhpInvalidInstanceofInspection */',
            "\t" . sprintf('if ($this->%1$s instanceof %2$s) {', $property->getName(), $property->getType()),
            "\t" . "\t" . sprintf('$this->%1$s = [$this->%1$s];', $property->getName()),
            "\t" . '} else {',
            "\t" . "\t" . sprintf('$this->%s = [];', $property->getName()),
            "\t" . '}',
            '}',
            ''
        ];
        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);

        $class->addMethodFromGenerator($methodGenerator);
    }

    protected function getAsArrayOfMethodName(Property $property): string
    {
        return Normalizer::normalizeMethodName(sprintf('asArrayOf%s', $property->getName()));
    }

    private function applyPropertyTypePatch(Generator\ClassGenerator $class, Property $property)
    {
        $class->removeProperty($property->getName());
        $class->addPropertyFromGenerator(
            Generator\PropertyGenerator::fromArray([
                'name' => $property->getName(),
                'visibility' => Generator\PropertyGenerator::VISIBILITY_PRIVATE,
                'omitdefaultvalue' => true,
                'docblock' => DocBlockGeneratorFactory::fromArray([
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => sprintf('%s[]', $property->getType()),
                        ],
                    ]
                ])
            ])
        );
    }

    private function applyAsArrayOfPath(Generator\ClassGenerator $class, Property $property, string $methodName)
    {
        if (!$class->hasMethod($methodName)) {
            return;
        }
        $method = $class->getMethod($methodName);

        $class->removeMethod($method->getName());

        $lines = [
            sprintf('$this->%s();', $this->getAsArrayOfMethodName($property)),
            '',
            $method->getBody()
        ];

        $body = implode($class::LINE_FEED, $lines);

        $method->setBody($body);
        $class->addMethodFromGenerator($method);
    }

    private function applyGetterPatch(Generator\ClassGenerator $class, Property $property)
    {
        if (!$this->options->getGetterOptions()->useBoolGetters()) {
            $prefix = 'get';
        } else {
            $prefix = $property->getType() === 'bool' ? 'is' : 'get';
        }
        $methodName = Normalizer::generatePropertyMethod($prefix, $property->getName());

        if (!$class->hasMethod($methodName)) {
            return;
        }

        $this->applyAsArrayOfPath($class, $property, $methodName);

        if ($this->options->getGetterOptions()->useReturnType()
            || $this->options->getGetterOptions()->useDocBlocks()) {
            $method = $class->getMethod($methodName);
            $class->removeMethod($method->getName());

            if ($this->options->getGetterOptions()->useReturnType()) {
                $method->setReturnType('array');
            }

            if ($this->options->getGetterOptions()->useDocBlocks()) {
                $method->setDocBlock(
                    DocBlockGeneratorFactory::fromArray([
                        'tags' => [
                            new Generator\DocBlock\Tag\ReturnTag(
                                [sprintf('%s[]', $property->getType())]
                            ),
                        ],
                    ])
                );
            }

            $class->addMethodFromGenerator($method);
        }
    }

    private function getSetterPatchCode(Generator\ClassGenerator $class, Property $property): string
    {
        $lines = [
            sprintf('if (!is_array($%s)) {', $property->getName()),
            "\t" . sprintf('$%1$s = [$%1$s];', $property->getName()),
            '}',
            '',
            sprintf('foreach ($%1$s as $%1$sItem) {', $property->getName()),
            "\t" . sprintf('if (!($%1$sItem instanceof %2$s)) {', $property->getName(), $property->getType()),
            "\t" . "\t" . 'throw new \InvalidArgumentException(',
            "\t" . "\t" . "\t" . 'sprintf(',
            "\t" . "\t" . "\t" . "\t" . sprintf(
                '\'The %1$s property can only contain items of %2$s type , %%s given\',',
                $property->getName(),
                $property->getType()
            ),
            "\t" . "\t" . "\t" . "\t" . sprintf('is_object($%1$sItem)', $property->getName()),
            "\t" . "\t" . "\t" . "\t" . "\t" . sprintf('? get_class($%1$sItem)', $property->getName()),
            "\t" . "\t" . "\t" . "\t" . "\t" . sprintf(
                ': sprintf(\'%%1$s(%%2$s)\', gettype($%1$sItem), var_export($%1$sItem, true))',
                $property->getName()
            ),
            "\t" . "\t" . "\t" . ')',
            "\t" . "\t" . ' );',
            "\t" . '}',
            '}'
        ];

        return implode($class::LINE_FEED, $lines);
    }
}