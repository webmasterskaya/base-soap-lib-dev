<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;

class PropertyAssembler extends \Phpro\SoapClient\CodeGenerator\Assembler\PropertyAssembler
{
    /**
     * @param \Phpro\SoapClient\CodeGenerator\Context\PropertyContext$context
     * @return void
     */
    public function assemble(ContextInterface $context)
    {
        var_dump($context->getType()->getName());
        parent::assemble($context);
    }
}