<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\PropertyContext;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\CodeGenerator\LaminasCodeFactory\DocBlockGeneratorFactory;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;

class ArrayTypePatchAssembler implements AssemblerInterface
{

    private $options;

    public function __construct(ArrayTypePatchAssemblerOptions $options = null)
    {
        $this->options = $options ?? new ArrayTypePatchAssemblerOptions();
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof TypeContext || $context instanceof PropertyContext;
    }

    public function assemble(ContextInterface $context)
    {
        if ($context instanceof TypeContext) {
            $this->assembleType($context);
        }

        if ($context instanceof PropertyContext) {
            $this->assembleProperty($context);
        }
    }

    public function assembleType(TypeContext $context)
    {
        $class = $context->getClass();
        $properties = $context->getType()->getProperties();
        $firstProperty = count($properties) ? current($properties) : null;

        try {
            $this->implementPropertyArrayPatch($class, $firstProperty);
            $this->patchTypeProperty($class, $firstProperty);

            if ($this->options->useArrayAccessPatch()) {
                $this->patchMethod($class, $firstProperty, 'offsetExists');
                $this->patchMethod($class, $firstProperty, 'offsetGet');
                $this->patchMethod($class, $firstProperty, 'offsetSet');
                $this->patchMethod($class, $firstProperty, 'offsetUnset');
            }

            if ($this->options->useCountablePatch()) {
                $this->patchMethod($class, $firstProperty, 'count');
            }

            if ($this->options->useIteratorPatch()) {
                $this->patchMethod($class, $firstProperty, 'getIterator');
            }

            if ($this->options->useGetterPatch()) {
                $getterName = '';
                $this->patchMethod($class, $firstProperty, $getterName);
            }

            if ($this->options->useSetterPatch()) {
                $setterName = '';
                $this->patchMethod($class, $firstProperty, $setterName);
            }

            if ($this->options->useFluentSetterPatch()) {
                $fluentSetterName = '';
                $this->patchMethod($class, $firstProperty, $fluentSetterName);
            }

            if ($this->options->useImmutableSetterPatch()) {
                $immutableSetterName = '';
                $this->patchMethod($class, $firstProperty, $immutableSetterName);
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    public function assembleProperty(PropertyContext $context)
    {
        $class = $context->getClass();

        try {

        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    private function implementPropertyArrayPatch(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = $this->getPatchPropertyMethodName($firstProperty->getName());
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PRIVATE);

        $lines = [
            sprintf('if (!is_array($this->%s)) {', $firstProperty->getName()),
            '/** @noinspection PhpInvalidInstanceofInspection */',
            "\t" . sprintf('if ($this->%1$s instanceof %2$s) {', $firstProperty->getName(), $firstProperty->getType()),
            "\t" . "\t" . sprintf('$this->%1$s = [$this->%1$s];', $firstProperty->getName()),
            "\t" . '} else {',
            "\t" . "\t" . sprintf('$this->%s = [];', $firstProperty->getName()),
            "\t" . '}',
            '}',
            ''
        ];
        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);

        $class->addMethodFromGenerator($methodGenerator);
    }

    protected function getPatchPropertyMethodName(string $property): string
    {
        return Normalizer::normalizeMethodName(
            lcfirst(Normalizer::normalizeProperty($property)) . 'ArrayPatch'
        );
    }

    private function patchTypeProperty(ClassGenerator $class, Property $firstProperty)
    {
        $class->removeProperty($firstProperty->getName());
        $class->addPropertyFromGenerator(
            PropertyGenerator::fromArray([
                'name' => $firstProperty->getName(),
                'visibility' => PropertyGenerator::VISIBILITY_PRIVATE,
                'omitdefaultvalue' => true,
                'docblock' => DocBlockGeneratorFactory::fromArray([
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => sprintf('%s[]', $firstProperty->getType()),
                        ],
                    ]
                ])
            ])
        );
    }

    private function patchMethod(ClassGenerator $class, Property $property, string $methodName)
    {
        if (!$class->hasMethod($methodName)) {
            return;
        }
        $method = $class->getMethod($methodName);

        $class->removeMethod($method->getName());

        $lines = [
            sprintf('$this->%s();', $this->getPatchPropertyMethodName($property->getName())),
            '',
            $method->getBody()
        ];

        $body = implode($class::LINE_FEED, $lines);

        $method->setBody($body);
        $class->addMethodFromGenerator($method);
    }
}