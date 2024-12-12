<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\VarTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Phpro\SoapClient\CodeGenerator\Context\ClientContext;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\Exception\AssemblerException;
use Webmasterskaya\Soap\Base\Caller\CallerInterface;

use function Psl\Type\non_empty_string;

class ClientConstructorAssembler extends \Phpro\SoapClient\CodeGenerator\Assembler\ClientConstructorAssembler
{
    public function assemble(ContextInterface $context)
    {
        if (!$context instanceof ClientContext) {
            throw new AssemblerException(
                __METHOD__ . ' expects an ' . ClientContext::class . ' as input ' . get_class($context) . ' given'
            );
        }

        $class = $context->getClass();
        try {
            $caller = $this->generateClassNameAndAddImport(CallerInterface::class, $class);
            $class->addPropertyFromGenerator(
                (new PropertyGenerator(
                    name: 'caller',
                    flags: AbstractMemberGenerator::FLAG_PRIVATE,
                    type: TypeGenerator::fromTypeString($caller)
                ))
                    ->setDocBlock(new DocBlockGenerator(tags: [new VarTag(description: $caller)]))
                    ->omitDefaultValue(true)
            );
            $class->addMethodFromGenerator(
                (new MethodGenerator(
                    name: '__construct',
                    parameters: [
                        new ParameterGenerator('caller', CallerInterface::class)
                    ],
                    body: '$this->caller = $caller;'
                ))
            );
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }

        return true;
    }

    /**
     * @param non-empty-string $fqcn
     */
    private function generateClassNameAndAddImport(string $fqcn, ClassGenerator $class): string
    {
        $fqcn = non_empty_string()->assert(ltrim($fqcn, '\\'));
        $parts = explode('\\', $fqcn);
        $className = array_pop($parts);

        if (!\in_array($fqcn, $class->getUses(), true)) {
            $class->addUse($fqcn);
        }

        return $className;
    }
}