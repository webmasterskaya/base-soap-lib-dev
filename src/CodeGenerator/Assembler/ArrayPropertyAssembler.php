<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Assembler\IteratorAssembler;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;

class ArrayPropertyAssembler implements AssemblerInterface
{

    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof TypeContext;
    }

    public function assemble(ContextInterface $context)
    {
        $class = $context->getClass();
        $properties = $context->getType()->getProperties();
        $firstProperty = count($properties) ? current($properties) : null;

        try {
            $iteratorAssembler = new IteratorAssembler();
            if ($iteratorAssembler->canAssemble($context)) {
                $iteratorAssembler->assemble($context);
            }

            $arrayAccessAssembler = new ArrayAccessAssembler();
            if ($arrayAccessAssembler->canAssemble($context)) {
                $arrayAccessAssembler->assemble($context);
            }

            $countableAssembler = new CountableAssembler();
            if ($countableAssembler->canAssemble($context)) {
                $countableAssembler->assemble($context);
            }

            if ($firstProperty) {
                $this->implementGetter($class, $firstProperty);
                $this->implementImmutableSetter($class, $firstProperty);
                $this->implementSetter($class, $firstProperty);

                $this->implementPropertyAsArray($class, $firstProperty);

                $class->removeProperty($firstProperty->getName());
                $class->addPropertyFromGenerator(
                    PropertyGenerator::fromArray([
                        'name' => $firstProperty->getName(),
                        'visibility' => PropertyGenerator::VISIBILITY_PRIVATE,
                        'omitdefaultvalue' => true,
                        'docblock' => DocBlockGeneratorFactory::fromArray([
                            'tags' => [
                                [
                                    'name' => 'var',
                                    'description' => sprintf('%s[]', $firstProperty->getType()),
                                ],
                            ]
                        ])
                    ])
                );
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    private function implementPropertyAsArray(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = $this->getPropertyAsArrayMethodName($firstProperty->getName());
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PRIVATE);

        $lines = [
            sprintf('if (!is_array($this->%s)) {', $firstProperty->getName()),
            '/** @noinspection PhpInvalidInstanceofInspection */',
            "\t" . sprintf('if ($this->%1$s instanceof %2$s) {', $firstProperty->getName(), $firstProperty->getType()),
            "\t" . "\t" . sprintf('$this->%1$s = [$this->%1$s];', $firstProperty->getName()),
            "\t" . '} else {',
            "\t" . "\t" . sprintf('$this->%s = [];', $firstProperty->getName()),
            "\t" . '}',
            '}',
            ''
        ];
        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);

        $class->addMethodFromGenerator($methodGenerator);
    }

    private function implementGetter(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = Normalizer::generatePropertyMethod('get', $firstProperty->getName());
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);

        $lines = [
            sprintf('$this->%s();', $this->getPropertyAsArrayMethodName($firstProperty->getName())),
            '',
            sprintf('return $this->%s;', $firstProperty->getName()),
        ];
        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $methodGenerator->setDocBlock(
            DocBlockGeneratorFactory::fromArray([
                'tags' => [
                    [
                        'name' => 'return',
                        'description' => sprintf('%s[]', $firstProperty->getType()),
                    ],
                ],
            ])
        );
        $methodGenerator->setReturnType('array');

        $class->addMethodFromGenerator($methodGenerator);
    }

    private function implementSetter(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = Normalizer::generatePropertyMethod('set', $firstProperty->getName());
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setParameters([['name' => $firstProperty->getName()]]);
        $methodGenerator->setReturnType($class->getNamespaceName() . '\\' . $class->getName());
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => sprintf(
                    'Set the %s property value.',
                    $firstProperty->getName()
                ),
                'longDescription' => sprintf(
                    'The method will set the %s property and will return the current instance to enable chaining.',
                    $firstProperty->getName()
                ),
                'tags' => [
                    new Tag\ParamTag(
                        $firstProperty->getName(),
                        [$firstProperty->getType(), sprintf('%s[]', $firstProperty->getType())]
                    ),
                    new Tag\ReturnTag([$class->getName()])
                ]
            ])
        );

        $lines = [
            sprintf('if (!is_array($%s)) {', $firstProperty->getName()),
            "\t" . sprintf('$%1$s = [$%1$s];', $firstProperty->getName()),
            '}',
            '',
            sprintf('foreach ($%1$s as $%1$sItem) {', $firstProperty->getName()),
            "\t" . sprintf('if (!($%1$sItem instanceof %2$s)) {', $firstProperty->getName(), $firstProperty->getType()),
            "\t" . "\t" . 'throw new \InvalidArgumentException(',
            "\t" . "\t" . "\t" . 'sprintf(',
            "\t" . "\t" . "\t" . "\t" . sprintf(
                '\'The %1$s property can only contain items of %2$s type , %%s given\',',
                $firstProperty->getName(),
                $firstProperty->getType()
            ),
            "\t" . "\t" . "\t" . "\t" . sprintf('is_object($%1$sItem)', $firstProperty->getName()),
            "\t" . "\t" . "\t" . "\t" . "\t" . sprintf('? get_class($%1$sItem)', $firstProperty->getName()),
            "\t" . "\t" . "\t" . "\t" . "\t" . sprintf(
                ': sprintf(\'%%1$s(%%2$s)\', gettype($%1$sItem), var_export($%1$sItem, true))',
                $firstProperty->getName()
            ),
            "\t" . "\t" . "\t" . ')',
            "\t" . "\t" . ' );',
            "\t" . '}',
            '}',
            '',
            sprintf('$this->%1$s = $%1$s;', $firstProperty->getName()),
            '',
            'return $this;'
        ];

        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $class->addMethodFromGenerator($methodGenerator);
    }

    private function implementImmutableSetter(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = Normalizer::generatePropertyMethod('with', $firstProperty->getName());
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setParameters([['name' => $firstProperty->getName()]]);
        $methodGenerator->setReturnType($class->getNamespaceName() . '\\' . $class->getName());
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => sprintf(
                    'Set the %s property value.',
                    $firstProperty->getName()
                ),
                'longDescription' => sprintf(
                    'The method will set the %s property and will return a new instance with the new value set. Used to create variations of the same base instance, without modifying the original values.',
                    $firstProperty->getName()
                ),
                'tags' => [
                    new Tag\ParamTag(
                        $firstProperty->getName(),
                        [$firstProperty->getType(), sprintf('%s[]', $firstProperty->getType())]
                    ),
                    new Tag\ReturnTag([$class->getName()])
                ]
            ])
        );

        $setterName = Normalizer::generatePropertyMethod('set', $firstProperty->getName());
        $lines = [
            '$new = clone $this;',
            sprintf('$new->%1$s($%2$s);', $setterName, $firstProperty->getName()),
            '',
            'return $new;'
        ];

        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $class->addMethodFromGenerator($methodGenerator);
    }

    protected function getPropertyAsArrayMethodName(string $property): string
    {
        return Normalizer::normalizeMethodName(
            lcfirst(Normalizer::normalizeProperty($property)) . 'AsArray'
        );
    }
}