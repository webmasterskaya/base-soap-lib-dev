<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;
use Laminas\Code\Generator\DocBlock\Tag\ReturnTag;
use Laminas\Code\Generator\DocBlock\Tag\ThrowsTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Phpro\SoapClient\CodeGenerator\Context\ClientMethodContext;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\GeneratorInterface;
use Phpro\SoapClient\CodeGenerator\Model\ClientMethod;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Webmasterskaya\Soap\Base\Dev\Exception\AssemblerException;
use Webmasterskaya\Soap\Base\Exception\SoapException;
use Webmasterskaya\Soap\Base\Type\MultiArgumentRequest;
use Webmasterskaya\Soap\Base\Type\RequestInterface;
use Webmasterskaya\Soap\Base\Type\ResultInterface;

class ClientMethodAssembler extends \Phpro\SoapClient\CodeGenerator\Assembler\ClientMethodAssembler
{
    public function assemble(ContextInterface $context): bool
    {
        if (!$context instanceof ClientMethodContext) {
            throw new AssemblerException(
                __METHOD__ . ' expects an ' . ClientMethodContext::class . ' as input ' . get_class($context) . ' given'
            );
        }
        $class = $context->getClass();
        $method = $context->getMethod();
        try {
            $phpMethodName = Normalizer::normalizeMethodName($method->getMethodName());
            $param = $this->createParamsFromContext($context);
            $class->removeMethod($phpMethodName);
            // TODO: Разобраться с ClientGenerator и убрать эту зачистку неймспейсов
            $class
                ->removeUse('Phpro\SoapClient\Type\ResultInterface')
                ->removeUse('Phpro\SoapClient\Exception\SoapException')
                ->removeUse('Phpro\SoapClient\Type\RequestInterface')
                ->removeUse('Phpro\SoapClient\Caller\Caller');
            $docblock = $method->shouldGenerateAsMultiArgumentsRequest()
                ? $this->generateMultiArgumentDocblock($context)
                : $this->generateSingleArgumentDocblock($context);
            $methodBody = $this->generateMethodBody($class, $param, $method, $context);

            $class->addMethodFromGenerator(
                (new MethodGenerator(
                    name: $phpMethodName,
                    parameters: $param === null ? [] : [$param],
                    body: $methodBody,
                    docBlock: $docblock
                ))->setReturnType($this->decideOnReturnType($context, true))
            );
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }

        return true;
    }

    private function generateMethodBody(
        ClassGenerator $class,
        ?ParameterGenerator $param,
        ClientMethod $method,
        $context
    ): string {
        $assertInstanceOf = static fn(string $class): string => '\\Psl\\Type\\instance_of(\\' . ltrim(
                $class,
                '\\'
            ) . '::class)->assert($response);';

        $code = [
            sprintf(
                '/** @var %s $response */',
                $this->decideOnReturnType($context, true)
            ),
            sprintf(
                '$response = ($this->caller)(\'%s\', %s);',
                $method->getMethodName(),
                $param === null
                    ? 'new ' . $this->generateClassNameAndAddImport(MultiArgumentRequest::class, $class) . '([])'
                    : '$' . $param->getName()
            ),
            '',
            $assertInstanceOf($this->decideOnReturnType($context, true)),
            $assertInstanceOf(ResultInterface::class),
            '',
            'return $response;',
        ];

        return implode($class::LINE_FEED, $code);
    }

    /**
     * @param ClientMethodContext $context
     *
     * @return ParameterGenerator|null
     */
    private function createParamsFromContext(ClientMethodContext $context): ?ParameterGenerator
    {
        $method = $context->getMethod();
        $paramsCount = $method->getParametersCount();

        if ($paramsCount === 0) {
            return null;
        }

        if (!$method->shouldGenerateAsMultiArgumentsRequest()) {
            $param = current($context->getMethod()->getParameters());

            return new ParameterGenerator(...$param->toArray());
        }

        return new ParameterGenerator(name: 'multiArgumentRequest', type: MultiArgumentRequest::class);
    }

    /**
     * @param ClientMethodContext $context
     *
     * @return DocBlockGenerator
     */
    private function generateMultiArgumentDocblock(ClientMethodContext $context): DocBlockGenerator
    {
        $class = $context->getClass();
        $description = ['MultiArgumentRequest with following params:' . GeneratorInterface::EOL];
        foreach ($context->getMethod()->getParameters() as $parameter) {
            $description[] = $parameter->getType() . ' $' . $parameter->getName();
        }

        return new DocBlockGenerator(
            shortDescription: $context->getMethod()->getMeta()->docs()->unwrapOr(''),
            longDescription: implode(GeneratorInterface::EOL, $description),
            tags: [
                new ParamTag(
                    description: sprintf(
                        '%s $%s',
                        $this->generateClassNameAndAddImport(
                            MultiArgumentRequest::class,
                            $class
                        ),
                        'multiArgumentRequest'
                    )
                ),
                new ReturnTag(
                    description: sprintf(
                        '%s & %s',
                        $this->generateClassNameAndAddImport(ResultInterface::class, $class),
                        $this->decideOnReturnType($context, false)
                    )
                ),
                new ThrowsTag(
                    description: $this->generateClassNameAndAddImport(
                        SoapException::class,
                        $class
                    )
                )
            ]
        );
    }

    /**
     * @param ClientMethodContext $context
     *
     * @return DocBlockGenerator
     */
    private function generateSingleArgumentDocblock(ClientMethodContext $context): DocBlockGenerator
    {
        $method = $context->getMethod();
        $class = $context->getClass();
        $param = current($method->getParameters());

        $tags = [
            new ReturnTag(
                description: sprintf(
                    '%s & %s',
                    $this->generateClassNameAndAddImport(ResultInterface::class, $class),
                    $this->decideOnReturnType($context, false)
                )
            ),
            new ThrowsTag(
                description: $this->generateClassNameAndAddImport(
                    SoapException::class,
                    $class
                )
            )
        ];

        if ($param) {
            array_unshift(
                $tags,
                new ParamTag(
                    description: sprintf(
                        '%s & %s $%s',
                        $this->generateClassNameAndAddImport(RequestInterface::class, $class),
                        $this->generateClassNameAndAddImport($param->getType(), $class, true),
                        $param->getName()
                    )
                )
            );
        }

        return (new DocBlockGenerator(
            shortDescription: $context->getMethod()->getMeta()->docs()->unwrapOr(''),
            tags: $tags
        ))->setWordWrap(false);
    }
}