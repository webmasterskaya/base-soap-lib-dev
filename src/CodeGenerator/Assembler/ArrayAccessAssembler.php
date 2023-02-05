<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Assembler\InterfaceAssembler;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\TypeContext;
use Phpro\SoapClient\CodeGenerator\Model\Property;
use Phpro\SoapClient\Exception\AssemblerException;

class ArrayAccessAssembler implements AssemblerInterface
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

    /**
     * @inheritDoc
     */
    public function assemble(ContextInterface $context)
    {
        $class = $context->getClass();
        $properties = $context->getType()->getProperties();
        $firstProperty = count($properties) ? current($properties) : null;

        try {
            $interfaceAssembler = new InterfaceAssembler(\ArrayAccess::class);
            if ($interfaceAssembler->canAssemble($context)) {
                $interfaceAssembler->assemble($context);
            }

            if ($firstProperty) {
                $this->implementOffsetExists($class, $firstProperty);
                $this->implementOffsetGet($class, $firstProperty);
                $this->implementOffsetSet($class, $firstProperty);
                $this->implementOffsetUnset($class, $firstProperty);
            }
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    private function implementOffsetExists(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = 'offsetExists';
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setParameters([['name' => 'offset']]);
        $methodGenerator->setReturnType('bool');
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => 'Whether a offset exists',
                'longDescription' => 'The return value will be casted to boolean if non-boolean was returned.',
                'tags' => [
                    new Tag\ParamTag(
                        'offset',
                        ['mixed'],
                        'An offset to check for.'
                    ),
                    new Tag\ReturnTag(['bool'], 'true on success or false on failure.'),
                    new Tag\GenericTag('link', 'https://php.net/manual/en/arrayaccess.offsetexists.php')
                ]
            ])
        );

        $methodGenerator->setBody(
            sprintf('return is_array($this->%1$s) && isset($this->%1$s[$offset]);', $firstProperty->getName())
        );
        $class->addMethodFromGenerator($methodGenerator);
    }

    private function implementOffsetGet(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = 'offsetGet';
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setParameters([['name' => 'offset']]);
        $methodGenerator->setReturnType(null);
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => 'Offset to retrieve',
                'longDescription' => null,
                'tags' => [
                    new Tag\ParamTag(
                        'offset',
                        ['mixed'],
                        'The offset to retrieve.'
                    ),
                    new Tag\ReturnTag(['mixed'], 'Can return all value types.'),
                    new Tag\GenericTag('link', 'https://php.net/manual/en/arrayaccess.offsetget.php')
                ]
            ])
        );

        $lines = [
            sprintf('return (is_array($this->%1$s) && isset($this->%1$s[$offset]))', $firstProperty->getName()),
            "\t" . sprintf('? $this->%1$s[$offset]', $firstProperty->getName()),
            "\t" . ': null;'
        ];

        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $class->addMethodFromGenerator($methodGenerator);
    }

    private function implementOffsetSet(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = 'offsetSet';
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setParameters([['name' => 'offset'], ['name' => 'value']]);
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => 'Offset to set',
                'longDescription' => null,
                'tags' => [
                    new Tag\ParamTag(
                        'offset',
                        ['mixed'],
                        'The offset to assign the value to.'
                    ),
                    new Tag\ParamTag(
                        'value',
                        ['mixed'],
                        'The value to set.'
                    ),
                    new Tag\ReturnTag(['void']),
                    new Tag\GenericTag('link', 'https://php.net/manual/en/arrayaccess.offsetset.php')
                ]
            ])
        );

        $lines = [
            sprintf('if (!($value instanceof %s)) {', $firstProperty->getType()),
            "\t" . 'throw new \InvalidArgumentException(',
            "\t" . "\t" . 'sprintf(',
            "\t" . "\t" . "\t" . sprintf(
                '\'The %1$s property can only contain items of type %2$s, %%s given\',',
                $firstProperty->getName(),
                $firstProperty->getType()
            ),
            "\t" . "\t" . "\t" . 'is_object($value)',
            "\t" . "\t" . "\t" . "\t" . '? get_class($value)',
            "\t" . "\t" . "\t" . "\t" . ': sprintf(\'%1$s(%2$s)\', gettype($value), var_export($value, true))',
            "\t" . "\t" . ')',
            "\t" . ');',
            '}',
            '',
            sprintf('if(!is_array($this->%1$s) && !is_null($this->%1$s)) {', $firstProperty->getName()),
            "\t" . '/** @noinspection PhpInvalidInstanceofInspection */',
            "\t" . 'throw new \RuntimeException(',
            "\t" . "\t" . 'sprintf(',
            "\t" . "\t" . "\t" . sprintf(
                '\'The property %1$s::$%2$s must be array, %%s given\',',
                $class->getName(),
                $firstProperty->getName()
            ),
            "\t" . "\t" . "\t" . sprintf('is_object($this->%1$s)', $firstProperty->getName()),
            "\t" . "\t" . "\t" . "\t" . sprintf('? get_class($this->%1$s)', $firstProperty->getName()),
            "\t" . "\t" . "\t" . "\t" . sprintf(
                ': sprintf(\'%%1$s(%%2$s)\', gettype($this->%1$s), var_export($this->%1$s, true))',
                $firstProperty->getName()
            ),
            "\t" . "\t" . ')',
            "\t" . ');',
            '}',
            '',
            'if (is_null($offset)) {',
            "\t" . sprintf('$this->%s[] = $value;', $firstProperty->getName()),
            '} else {',
            "\t" . sprintf('$this->%s[$offset] = $value;', $firstProperty->getName()),
            '}'
        ];
        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $class->addMethodFromGenerator($methodGenerator);
    }

    private function implementOffsetUnset(ClassGenerator $class, Property $firstProperty)
    {
        $methodName = 'offsetUnset';
        $class->removeMethod($methodName);

        $methodGenerator = new MethodGenerator($methodName);
        $methodGenerator->setVisibility(MethodGenerator::VISIBILITY_PUBLIC);
        $methodGenerator->setParameters([['name' => 'offset']]);
        $methodGenerator->setDocBlock(
            DocBlockGenerator::fromArray([
                'shortDescription' => 'Offset to unset',
                'longDescription' => null,
                'tags' => [
                    new Tag\ParamTag(
                        'offset',
                        ['mixed'],
                        'The offset to unset.'
                    ),
                    new Tag\ReturnTag(['void']),
                    new Tag\GenericTag('link', 'https://php.net/manual/en/arrayaccess.offsetunset.php')
                ]
            ])
        );

        $lines = [
            sprintf('if(is_array($this->%1$s)) {', $firstProperty->getName()),
            "\t" . sprintf('unset($this->%1$s[$offset]);', $firstProperty->getName()),
            '}',
        ];
        $body = implode($class::LINE_FEED, $lines);

        $methodGenerator->setBody($body);
        $class->addMethodFromGenerator($methodGenerator);
    }
}