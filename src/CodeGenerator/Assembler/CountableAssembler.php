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

        $methodGenerator = (new MethodGenerator($methodName))
            ->setReturnType('int')
            ->setDocBlock(
                new DocBlockGenerator(
                    'Count elements of an object',
                    'The return value is cast to an integer.',
                    [
                        new Tag\ReturnTag('int'),
                        new Tag\GenericTag('link', 'https://php.net/manual/en/countable.count.php')
                    ]
                )
            );

        $methodGenerator->setBody(
            sprintf('return is_array($this->%1$s) ? count($this->%1$s) : 0;', $firstProperty->getName())
        );
        $class->addMethodFromGenerator($methodGenerator);
    }
}