<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Traversable;

class TraversableFixAssembler implements AssemblerInterface
{
    public function assemble(ContextInterface $context)
    {
        /** @var ClassGenerator $class */
        $class = $context->getClass();
        if ($class->hasImplementedInterface(Traversable::class)) {
            $class->removeImplementedInterface(Traversable::class);
        }
    }

    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof TypeContext;
    }
}
