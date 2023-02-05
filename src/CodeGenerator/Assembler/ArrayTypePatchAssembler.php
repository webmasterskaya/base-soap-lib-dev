<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Phpro\SoapClient\CodeGenerator\Assembler;
use Phpro\SoapClient\CodeGenerator\Context;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;

class ArrayTypePatchAssembler implements Assembler\AssemblerInterface
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
            $this->implementPropertyArrayPatch($class, $firstProperty);

            if ($this->options->useArrayAccessPatch()) {
                $this->patchMethod($class, $firstProperty, 'offsetExists');
                $this->patchMethod($class, $firstProperty, 'offsetGet');
                $this->patchMethod($class, $firstProperty, 'offsetSet');
                $this->patchMethod($class, $firstProperty, 'offsetUnset');
            }

            if ($this->options->useCountablePatch()) {
                $this->patchMethod($class, $firstProperty, 'count');
            }

            if ($this->options->useIteratorPatch()) {
                $this->patchMethod($class, $firstProperty, 'getIterator');
            }

            if ($this->options->useGetterPatch()) {
                $getterName = '';
                $this->patchMethod($class, $firstProperty, $getterName);
            }

            if ($this->options->useSetterPatch()) {
                $setterName = '';
                $this->patchMethod($class, $firstProperty, $setterName);
            }

            if ($this->options->useFluentSetterPatch()) {
                $fluentSetterName = '';
                $this->patchMethod($class, $firstProperty, $fluentSetterName);
            }

            if ($this->options->useImmutableSetterPatch()) {
                $immutableSetterName = '';
                $this->patchMethod($class, $firstProperty, $immutableSetterName);
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
            $this->patchTypeProperty($class, $property);

            if ($this->options->useGetterPatch()) {
                $getterAssembler = new Assembler\GetterAssembler($this->options->getGetterOptions());
                if ($getterAssembler->canAssemble($context)) {
                    $getterAssembler->assemble($context);
                    $this->patchGetter($class, $property);
                }
            }


            if ($this->options->useSetterPatch()
                || $this->options->useFluentSetterPatch()
                || $this->options->useImmutableSetterPatch()) {
                $this->implementSetterNormalizer($class, $property);

                if ($this->options->useSetterPatch()) {
                    $setterAssembler = new Assembler\SetterAssembler($this->options->getSetterOptions());
                    if ($setterAssembler->canAssemble($context)) {
                        $setterAssembler->assemble($context);
                        $methodName = Normalizer::generatePropertyMethod('set', $property->getName());
                        $this->patchSetter($class, $property, $methodName, $this->options->getSetterOptions());
                    }
                }

                if ($this->options->useFluentSetterPatch()) {
                    $fluentSetterAssembler = new Assembler\FluentSetterAssembler(
                        $this->options->getFluentSetterOptions()
                    );
                    if ($fluentSetterAssembler->canAssemble($context)) {
                        $fluentSetterAssembler->assemble($context);
                        $methodName = Normalizer::generatePropertyMethod('set', $property->getName());
                        $this->patchSetter($class, $property, $methodName, $this->options->getFluentSetterOptions());
                    }
                }

                if ($this->options->useImmutableSetterPatch()) {
                    $immutableSetterAssembler = new Assembler\ImmutableSetterAssembler(
                        $this->options->getImmutableSetterOptions()
                    );
                    if ($immutableSetterAssembler->canAssemble($context)) {
                        $immutableSetterAssembler->assemble($context);
                        $methodName = Normalizer::generatePropertyMethod('with', $property->getName());
                        $this->patchSetter($class, $property, $methodName, $this->options->getImmutableSetterOptions());
                    }
                }
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    /**
     * @param Generator\ClassGenerator $class
     * @param Property $property
     * @param string $methodName
     * @param Assembler\SetterAssemblerOptions|Assembler\FluentSetterAssemblerOptions|Assembler\ImmutableSetterAssemblerOptions $options
     * @return void
     */
    private function patchSetter(Generator\ClassGenerator $class, Property $property, string $methodName, $options)
    {
        $normalizerMethodName = Normalizer::generatePropertyMethod('normalize', $property->getName());

        if (!$class->hasMethod($methodName)) {
            return;
        }
        $method = $class->getMethod($methodName);
        $class->removeMethod($method->getName());

        $lines = [
            sprintf('$%1$s = $this->%2$s($%1$s);', $property->getName(), $normalizerMethodName),
            '',
            $method->getBody()
        ];
        $body = implode($class::LINE_FEED, $lines);
        $method->setBody($body);

        if ($options && $options->useDocBlocks()) {
            $docBlock = $method->getDocBlock();
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

            $method->setDocBlock(Generator\DocBlockGenerator::fromArray($newDocBlock));
        }

        if (($options instanceof Assembler\FluentSetterAssemblerOptions && $options->useTypeHints())
            || ($options instanceof Assembler\ImmutableSetterAssemblerOptions && $options->useTypeHints())) {
            $method->setParameter(['name' => $property->getName()]);
        }

        $class->addMethodFromGenerator($method);
    }

    private function implementPropertyArrayPatch(Generator\ClassGenerator $class, Property $property)
    {
        $methodName = $this->getPatchPropertyMethodName($property->getName());
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

    protected function getPatchPropertyMethodName(string $property): string
    {
        return Normalizer::normalizeMethodName(
            lcfirst(Normalizer::normalizeProperty($property)) . 'ArrayPatch'
        );
    }

    private function patchTypeProperty(Generator\ClassGenerator $class, Property $property)
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

    private function patchMethod(Generator\ClassGenerator $class, Property $property, string $methodName)
    {
        if (!$class->hasMethod($methodName)) {
            return;
        }
        $method = $class->getMethod($methodName);

        $class->removeMethod($method->getName());

        $lines = [
            sprintf('$this->%s();', $this->getPatchPropertyMethodName($property->getName())),
            '',
            $method->getBody()
        ];

        $body = implode($class::LINE_FEED, $lines);

        $method->setBody($body);
        $class->addMethodFromGenerator($method);
    }

    private function patchGetter(Generator\ClassGenerator $class, Property $property)
    {
        $methodName = Normalizer::generatePropertyMethod('get', $property->getName());

        if (!$class->hasMethod($methodName)) {
            return;
        }

        $this->patchMethod($class, $property, $methodName);

        $method = $class->getMethod($methodName);

        $class->removeMethod($method->getName());

        if ($this->options->getGetterOptions()->useReturnType()) {
            $method->setReturnType('array');
        }

        if ($this->options->getGetterOptions()->useDocBlocks()) {
            $method->setDocBlock(
                DocBlockGeneratorFactory::fromArray([
                    'tags' => [
                        [
                            'name' => 'return',
                            'description' => sprintf('%s[]', $property->getType()),
                        ],
                    ],
                ])
            );
        }

        $class->addMethodFromGenerator($method);
    }

    private function implementSetterNormalizer(Generator\ClassGenerator $class, Property $property)
    {
        $methodName = Normalizer::generatePropertyMethod('normalize', $property->getName());
        $class->removeMethod($methodName);

        $methodGenerator = new Generator\MethodGenerator($methodName);
        $methodGenerator->setVisibility(Generator\MethodGenerator::VISIBILITY_PRIVATE);
        $methodGenerator->setParameters([['name' => $property->getName()]]);

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
            '}',
            '',
            sprintf('return $%1$s;', $property->getName())
        ];

        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $class->addMethodFromGenerator($methodGenerator);
    }
}