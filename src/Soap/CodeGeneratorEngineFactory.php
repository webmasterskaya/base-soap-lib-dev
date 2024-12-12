<?php

namespace Webmasterskaya\Soap\Base\Dev\Soap;

use Soap\Engine\Engine;
use Soap\Engine\LazyEngine;
use Soap\Engine\NoopTransport;
use Soap\Engine\PartialDriver;
use Soap\Engine\SimpleEngine;
use Soap\Wsdl\Loader\FlatteningLoader;
use Soap\Wsdl\Loader\StreamWrapperLoader;
use Soap\Wsdl\Loader\WsdlLoader;
use Soap\WsdlReader\Locator\ServiceSelectionCriteria;
use Soap\WsdlReader\Metadata\Wsdl1MetadataProvider;
use Soap\WsdlReader\Model\Definitions\SoapVersion;
use Soap\WsdlReader\Parser\Context\ParserContext;
use Soap\WsdlReader\Wsdl1Reader;
use Webmasterskaya\Soap\Base\Soap\ExtSoap\Metadata\Manipulators\DuplicateTypes\IntersectDuplicateTypesStrategy;
use Webmasterskaya\Soap\Base\Soap\Metadata\MetadataFactory;
use Webmasterskaya\Soap\Base\Soap\Metadata\MetadataOptions;

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
        $loader ??= new FlatteningLoader(new StreamWrapperLoader());
        $metadataOptions ??= MetadataOptions::empty()->withTypesManipulator(
            new IntersectDuplicateTypesStrategy()
        );

        return new LazyEngine(static function () use (
            $wsdlLocation,
            $loader,
            $metadataOptions,
            $parserContext,
            $preferredSoapVersion
        ) {
            $wsdl = (new Wsdl1Reader($loader))($wsdlLocation, $parserContext);
            $metadataProvider = new Wsdl1MetadataProvider(
                $wsdl,
                ServiceSelectionCriteria::defaults()
                    ->withAllowHttpPorts(false)
                    ->withPreferredSoapVersion($preferredSoapVersion)
            );

            return new SimpleEngine(
                new PartialDriver(
                    metadata: MetadataFactory::manipulated($metadataProvider->getMetadata(), $metadataOptions),
                ),
                new NoopTransport()
            );
        });
    }
}