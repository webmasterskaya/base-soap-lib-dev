<?php

namespace Webmasterskaya\Soap\Base\Dev\Soap;

use Phpro\SoapClient\Soap\Metadata\MetadataOptions;
use Soap\Engine\Engine;
use Soap\Wsdl\Loader\WsdlLoader;
use Soap\WsdlReader\Model\Definitions\SoapVersion;
use Soap\WsdlReader\Parser\Context\ParserContext;

final class CodeGeneratorEngineFactory
{
    /**
     * @param non-empty-string $wsdlLocation
     */
    public static function create(
        string $wsdlLocation,
        ?WsdlLoader $loader = null,
        ?MetadataOptions $metadataOptions = null,
        ?SoapVersion $preferredSoapVersion = null,
        ?ParserContext $parserContext = null,
    ): Engine {
        return \Phpro\SoapClient\Soap\CodeGeneratorEngineFactory::create(
            $wsdlLocation,
            $loader,
            $metadataOptions,
            $preferredSoapVersion,
            $parserContext,
        );
    }
}
