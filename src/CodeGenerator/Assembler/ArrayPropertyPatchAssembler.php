<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Reflection\ClassReflection;
use Phpro\SoapClient\CodeGenerator\Assembler;
use Phpro\SoapClient\CodeGenerator\Context;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;

class ArrayPropertyPatchAssembler implements Assembler\AssemblerInterface
{

    public function canAssemble(Context\ContextInterface $context): bool
    {
        return $context instanceof Context\PropertyContext;
    }

    /**
     * @param Context\PropertyContext $context
     * @return void
     */
    public function assemble(Context\ContextInterface $context)
    {
        $class = $context->getClass();
        $property = $context->getProperty();

        $this->patchFluentSetter($class, $property);
        $this->patchImmutableSetter($class, $property);
//        var_dump($class->getName(), $property->getName());
    }

    private function patchFluentSetter(Generator\ClassGenerator $class, Property $property)
    {
        $methodName = Normalizer::generatePropertyMethod('set', $property->getName());
        $this->patchMethod($class, $property, $methodName);
    }

    private function patchImmutableSetter(Generator\ClassGenerator $class, Property $property)
    {
        $methodName = Normalizer::generatePropertyMethod('with', $property->getName());
        $this->patchMethod($class, $property, $methodName);
    }

    private function patchMethod(Generator\ClassGenerator $class, Property $property, string $methodName)
    {
        if (!$class->hasMethod($methodName)) {
            return;
        }
        $method = $class->getMethod($methodName);

        $reflectionProperty = new ClassReflection($property->getType());
        $classProperty = Generator\ClassGenerator::fromReflection($reflectionProperty);
        $classPropertyProperties = $classProperty->getProperties();
        $classPropertyFirstProperty = count($classPropertyProperties) ? current($classPropertyProperties) : null;

        if (!$classPropertyFirstProperty) {
            return;
        }

        $classPropertyFluentSetterName = Normalizer::generatePropertyMethod(
            'set',
            $classPropertyFirstProperty->getName()
        );
        $classPropertyImmutableSetterName = Normalizer::generatePropertyMethod(
            'with',
            $classPropertyFirstProperty->getName()
        );

        if ($classProperty->hasMethod($classPropertyFluentSetterName)) {
            $propertyClassSetterName = $classPropertyFluentSetterName;
        } else {
            if ($classProperty->hasMethod($classPropertyImmutableSetterName)) {
                $propertyClassSetterName = $classPropertyImmutableSetterName;
            } else {
                return;
            }
        }

        $classPropertyGetterName = Normalizer::generatePropertyMethod(
            'get',
            $classPropertyFirstProperty->getName()
        );

        $class->removeMethod($method->getName());

        $lines = [
            sprintf('if (is_array($%1$s)) {', $property->getName()),
            "\t" . sprintf(
                '$%1$s = (new %2$s)->%3$s($%1$s);',
                $property->getName(),
                $classProperty->getName(),
                $propertyClassSetterName
            ),
            '}',
            '',
            sprintf('if (!$%1$s instanceof %2$s) {', $property->getName(), $property->getType()),
            "\t" . '',
            "\t" . 'throw new \InvalidArgumentException(',
            "\t" . "\t" . 'sprintf(',
            "\t" . "\t" . "\t" . sprintf(
                '\'The %1$s property must be of type %2$s, %%s given\',',
                $property->getName(),
                $property->getType()
            ),
            "\t" . "\t" . "\t" . sprintf('is_object($%1$s)', $property->getName()),
            "\t" . "\t" . "\t" . "\t" . sprintf('? get_class($%1$s)', $property->getName()),
            "\t" . "\t" . "\t" . "\t" . sprintf(
                ': sprintf(\'%%1$s(%%2$s)\', gettype($%1$s), var_export($%1$s, true))',
                $property->getName()
            ),
            "\t" . "\t" . ')',
            "\t" . ');',
            '}',
            '',
            $method->getBody()
        ];

        $body = implode($class::LINE_FEED, $lines);

        $method->setBody($body);

        $method->setParameter(['name' => $property->getName()]);

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
            [$property->getType(), 'array']
        );

        $method->setDocBlock(Generator\DocBlockGenerator::fromArray($newDocBlock));

        $class->addMethodFromGenerator($method);
    }
}