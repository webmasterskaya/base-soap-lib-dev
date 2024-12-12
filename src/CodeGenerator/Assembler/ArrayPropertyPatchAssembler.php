<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Phpro\SoapClient\CodeGenerator\Assembler;
use Phpro\SoapClient\CodeGenerator\Context;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\CodeGenerator\Model\Type;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Soap\Engine\Exception\MetadataException;
use Soap\Engine\Metadata\Metadata;

class ArrayPropertyPatchAssembler implements Assembler\AssemblerInterface
{

    /**
     * @var Metadata
     */
    private $metadata;

    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

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
        $typeProperty = $this->getTypeProperty($context);

        if ($typeProperty) {
            $patch = $this->getSetterPatchCode($class, $property, $typeProperty);
            $this->applySetterPatch($class, $property, $typeProperty, 'set', $patch);
            $this->applySetterPatch($class, $property, $typeProperty, 'with', $patch);
        }
    }

    /**
     * @param ClassGenerator $class
     * @param Property $property
     * @param string $prefix
     * @param string $code
     * @return void
     */
    private function applySetterPatch(Generator\ClassGenerator $class, Property $property, Property $typeProperty, string $prefix, string $code)
    {
        $methodName = Normalizer::generatePropertyMethod($prefix, $property->getName());

        if (!$class->hasMethod($methodName)) {
            return;
        }

        $method = $class->getMethod($methodName);
        $class->removeMethod($method->getName());
        $class->addUse($property->getType());
        $class->addUse($typeProperty->getType());

        $lines = [
            $code,
            '',
            $method->getBody()
        ];

        $body = implode($class::LINE_FEED, $lines);
        $method->setBody($body);

        $docBlock = $method->getDocBlock();
        if ($docBlock->getTags()) {
            $method->setDocBlock(
                Generator\DocBlockGenerator::fromArray($this->replaceDocblockParam($docBlock, $property, $typeProperty))
            );
        }

        $method->setParameter(['name' => $property->getName()]);
        $class->addMethodFromGenerator($method);
    }

    private function replaceDocblockParam(Generator\DocBlockGenerator $docBlock, Property $property, Property $typeProperty): array
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
            [Normalizer::getClassNameFromFQN($property->getType()), sprintf('%s[]', Normalizer::getClassNameFromFQN($typeProperty->getType()))]
        );

        return $newDocBlock;
    }

    private function getTypeProperty($context): ?Property
    {
        $property = $context->getProperty();

        try {
            $foundPropertyType = $this->metadata->getTypes()->fetchFirstByName(
                Normalizer::getClassNameFromFQN($property->getType())
            );
        } catch (MetadataException $e) {
            return null;
        }

        $propertyType = Type::fromMetadata(
            $context->getType()->getNamespace(),
            $foundPropertyType
        );

        $propertyTypeProperties = $propertyType->getProperties();
        /** @var Property $propertyTypeProperty */
        $propertyTypeProperty = count($propertyTypeProperties) ? current($propertyTypeProperties) : null;

        return $propertyTypeProperty;
    }

    private function getSetterPatchCode(
        Generator\ClassGenerator $class,
        Property $property,
        Property $typeProperty
    ): string {
        $propertyVarName = lcfirst(Normalizer::getClassNameFromFQN($property->getType()));
        $lines = [
            sprintf('if (!($%1$s instanceof %2$s)) {', $property->getName(), Normalizer::getClassNameFromFQN($property->getType())),
            "\t" . sprintf('$%1$s = new %2$s();', $propertyVarName, Normalizer::getClassNameFromFQN($property->getType())),
            "\t" . sprintf('$%1$s->%2$s($%3$s);', $propertyVarName, $typeProperty->setterName(), $property->getName()),
            "\t" . sprintf('$%1$s = $%2$s;', $property->getName(), $propertyVarName),
            '}'
        ];

        return implode($class::LINE_FEED, $lines);
    }
}