<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Assembler\InterfaceAssembler;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\Exception\AssemblerException;

class CountableAssembler implements AssemblerInterface
{
    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
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
            $countableAssembler = new InterfaceAssembler(\Countable::class);
            if ($countableAssembler->canAssemble($context)) {
                $countableAssembler->assemble($context);
            }

            if ($firstProperty) {
                $this->implementCount($class, $firstProperty);
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    private function implementCount($class, $firstProperty)
    {
        $methodName = 'count';
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setReturnType('int');
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => 'Count elements of an object',
                'longDescription' => 'The return value is cast to an integer.',
                'tags' => [
                    new Tag\ReturnTag([
                        'datatype' => 'int',
                    ]),
                    new Tag\GenericTag('link', 'https://php.net/manual/en/countable.count.php')
                ]
            ])
        );

        $methodGenerator->setBody(
            sprintf('return is_array($this->%1$s) ? count($this->%1$s) : 0;', $firstProperty->getName())
        );
        $class->addMethodFromGenerator($methodGenerator);
    }
}