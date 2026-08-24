<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Exception;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Context\ClassMapContext;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Model\TypeMap;
use Phpro\SoapClient\Exception\AssemblerException;
use Soap\ExtSoapEngine\Configuration\ClassMap\ClassMap;
use Soap\ExtSoapEngine\Configuration\ClassMap\ClassMapCollection;

class ClassMapAssembler implements AssemblerInterface
{
    /**
     * @param ClassMapContext $context
     *
     * @throws \Phpro\SoapClient\Exception\AssemblerException
     */
    public function assemble(ContextInterface $context): void
    {
        $class = new ClassGenerator($context->getName());
        $file = $context->getFile();
        $file->setClass($class);
        $file->setNamespace($context->getNamespace());
        $typeMap = $context->getTypeMap();
        $typeNamespace = $typeMap->getNamespace();
        $file->setUse($typeNamespace, preg_match('/\\\\Type$/', $typeNamespace) ? null : 'Type');

        try {
            $file->setUse(ClassMapCollection::class);
            $file->setUse(ClassMap::class);
            $linefeed = $file::LINE_FEED;
            $classMap = $this->assembleClassMap($typeMap, $linefeed, $file->getIndentation());
            $code = $this->assembleClassMapCollection($classMap, $linefeed) . $linefeed;
            $class->addMethodFromGenerator(
                (new MethodGenerator('__invoke', body: 'return ' . $code))
                    ->setReturnType(ClassMapCollection::class),
            );
        } catch (Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof ClassMapContext;
    }

    /***
     * @param TypeMap $typeMap
     * @param string $linefeed
     * @param string $indentation
     *
     * @return string
     */
    private function assembleClassMap(TypeMap $typeMap, string $linefeed, string $indentation): string
    {
        $classMap = [];
        foreach ($typeMap->getTypes() as $type) {
            $classMap[] = sprintf(
                '%snew ClassMap(\'%s\', %s::class),',
                $indentation,
                $type->getXsdName(),
                'Type\\' . $type->getName(),
            );
        }

        return implode($linefeed, $classMap);
    }

    private function assembleClassMapCollection(string $classMap, string $linefeed): string
    {
        $code = [
            'new ClassMapCollection(',
            '%s',
            ');',
        ];

        return sprintf(implode($linefeed, $code), $classMap);
    }
}
