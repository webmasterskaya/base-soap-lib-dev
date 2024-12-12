<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\CodeGenerator\Model\Type;
use Phpro\SoapClient\Exception\AssemblerException;

class ConstructorAssembler implements AssemblerInterface
{
    /**
     * @var ConstructorAssemblerOptions
     */
    private $options;

    /**
     * ConstructorAssembler constructor.
     *
     * @param ConstructorAssemblerOptions|null $options
     */
    public function __construct(ConstructorAssemblerOptions $options = null)
    {
        $this->options = $options ?? new ConstructorAssemblerOptions();
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof TypeContext;
    }

    /**
     * @param ContextInterface|TypeContext $context
     */
    public function assemble(ContextInterface $context)
    {
        $class = $context->getClass();
        $type = $context->getType();

        try {
            $class->removeMethod('__construct');
            $constructor = $this->assembleConstructor($type);
            $class->addMethodFromGenerator($constructor);
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    /**
     * @param Type $type
     *
     * @return MethodGenerator
     * @throws \Laminas\Code\Generator\Exception\InvalidArgumentException
     */
    private function assembleConstructor(Type $type): MethodGenerator
    {
        $body = [];
        $constructor = new MethodGenerator('__construct');
        $docblock = new DocBlockGenerator('Constructor');

        foreach ($type->getProperties() as $property) {
            $body[] = sprintf('$this->%1$s = $%1$s;', $property->getName());
            $withTypeHints = $this->options->useTypeHints() ? [
                'type' => sprintf(
                    '%1$s%2$s',
                    $this->options->withNullableParams() ? '?' : '',
                    $property->getType()
                )
            ] : [];

            $withDefaultValue = $this->options->withNullableParams() ? ['defaultvalue' => null] : [];

            $constructor->setParameter(
                array_merge([
                    'name' => $property->getName(),
                ], $withTypeHints, $withDefaultValue)
            );

            if ($this->options->useDocBlocks()) {
                $docblock->setTag([
                    'name' => 'var',
                    'description' => sprintf(
                        '%s%s $%s',
                        $property->getType(),
                        $this->options->withNullableParams() ? '|null' : '',
                        $property->getName()
                    )
                ]);
            }
        }

        if ($this->options->useDocBlocks()) {
            $constructor->setDocBlock($docblock);
        }

        $constructor->setBody(implode($constructor::LINE_FEED, $body));

        return $constructor;
    }
}