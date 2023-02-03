<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Phpro\SoapClient\CodeGenerator\Assembler\AssemblerInterface;
use Phpro\SoapClient\CodeGenerator\Context\ClassMapContext;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Model\TypeMap;
use Phpro\SoapClient\Exception\AssemblerException;
use Soap\ExtSoapEngine\Configuration\ClassMap\ClassMap;
use Soap\ExtSoapEngine\Configuration\ClassMap\ClassMapCollection;
use Webmasterskaya\Soap\Base\Soap\ExtSoap\Configuration\ClientClassMapCollectionInterface;

class ClassMapAssembler implements AssemblerInterface
{
    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof ClassMapContext;
    }

    /**
     * @param ClassMapContext|ContextInterface $context
     *
     * @throws \Phpro\SoapClient\Exception\AssemblerException
     */
    public function assemble(ContextInterface $context)
    {
        $class = ClassGenerator::fromArray(
            [
                'name' => $context->getName(),
                'implementedinterfaces' => [ClientClassMapCollectionInterface::class]
            ]
        );
        $file = $context->getFile();
        $file->setClass($class);
        $file->setNamespace($context->getNamespace());
        $typeMap = $context->getTypeMap();
        $typeNamespace = $typeMap->getNamespace();
        $file->setUse($typeNamespace, preg_match('/\\\\Type$/', $typeNamespace) ? null : 'Type');

        try {
            $file->setUse(ClassMapCollection::class);
            $file->setUse(ClassMap::class);
            $file->setUse(ClientClassMapCollectionInterface::class);
            $linefeed = $file::LINE_FEED;
            $classMap = $this->assembleClassMap($typeMap, $linefeed, $file->getIndentation());
            $code = $this->assembleClassMapCollection($classMap, $linefeed).$linefeed;
            $class->addMethodFromGenerator(
                MethodGenerator::fromArray(
                    [
                        'name'       => '__invoke',
                        'static'     => false,
                        'body'       => 'return '.$code,
                        'returntype' => ClassMapCollection::class,
                    ]
                )
            );
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }
    }

    /***
     * @param TypeMap $typeMap
     * @param string  $linefeed
     * @param string  $indentation
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
                'Type\\'.$type->getName()
            );
        }

        return implode($linefeed, $classMap);
    }

    /**
     * @param string $classMap
     * @param string $linefeed
     *
     * @return string
     */
    private function assembleClassMapCollection(string $classMap, string $linefeed): string
    {
        $code = [
            'new ClassMapCollection([',
            '%s',
            ']);',
        ];

        return sprintf(implode($linefeed, $code), $classMap);
    }
}