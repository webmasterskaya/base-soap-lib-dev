<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Context;
use Phpro\SoapClient\Exception\AssemblerException;

/**
 * @deprecated Instead, use different combinations of assemblers: IteratorAssembler, ArrayAccessAssembler, CountableAssembler, ArrayTypePatchAssembler, ArrayPropertyPatchAssembler
 */
class ArrayPropertyAssembler implements AssemblerInterface
{
    public function __construct()
    {
        trigger_error(sprintf('"%s" class is deprecated', get_class($this)), E_USER_DEPRECATED);
    }

    public function canAssemble(Context\ContextInterface $context): bool
    {
        return $context instanceof Context\TypeContext || $context instanceof Context\PropertyContext;
    }

    public function assemble(Context\ContextInterface $context)
    {
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

            $arrayTypePatchAssembler = new ArrayTypePatchAssembler();
            if ($arrayTypePatchAssembler->canAssemble($context)) {
                $arrayTypePatchAssembler->assemble($context);
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }
}