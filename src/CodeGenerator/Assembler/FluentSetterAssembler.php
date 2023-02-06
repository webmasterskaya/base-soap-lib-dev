<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\MethodGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\FluentSetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\PropertyContext;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;

class FluentSetterAssembler implements AssemblerInterface
{
    /**
     * @var FluentSetterAssemblerOptions
     */
    private $options;

    /**
     * FluentSetterAssembler constructor.
     *
     * @param FluentSetterAssemblerOptions|null $options
     */
    public function __construct(FluentSetterAssemblerOptions $options = null)
    {
        $this->options = $options ?? new FluentSetterAssemblerOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof PropertyContext;
    }

    public function assemble(ContextInterface $context)
    {
        $class = $context->getClass();
        $property = $context->getProperty();
        try {
            $methodName = Normalizer::generatePropertyMethod('set', $property->getName());
            $class->removeMethod($methodName);

            $parameterOptions = ['name' => $property->getName()];
            if ($this->options->useTypeHints()) {
                $parameterOptions['type'] = $property->getType();
            }

            $methodGenerator = new MethodGenerator($methodName);
            $methodGenerator->setParameters([$parameterOptions]);
            $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
            $methodGenerator->setBody(
                sprintf(
                    '$this->%1$s = $%1$s;%2$sreturn $this;',
                    $property->getName(),
                    $class::LINE_FEED
                )
            );
            if ($this->options->useReturnType()) {
                $methodGenerator->setReturnType($class->getNamespaceName() . '\\' . $class->getName());
            }
            if ($this->options->useDocBlocks()) {
                $methodGenerator->setDocBlock(
                    DocBlockGeneratorFactory::fromArray([
                        'tags' => [
                            [
                                'name' => 'param',
                                'description' => sprintf('%s $%s', $property->getType(), $property->getName()),
                            ],
                            [
                                'name' => 'return',
                                'description' => '$this',
                            ],
                        ],
                    ])
                );
            }
            $class->addMethodFromGenerator($methodGenerator);
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }
}