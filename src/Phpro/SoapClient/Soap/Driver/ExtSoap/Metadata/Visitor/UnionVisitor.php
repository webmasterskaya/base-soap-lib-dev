<?php

declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Driver\ExtSoap\Metadata\Visitor;

use Phpro\SoapClient\Soap\Engine\Metadata\Model\XsdType;

class UnionVisitor implements XsdTypeVisitorInterface
{
    public function __invoke(string $soapType): ?XsdType
    {
        if (!preg_match('/^union (?P<typeName>\w+)( \{(?P<memberTypes>[^\}]+)\})?$/', $soapType, $matches)) {
            return null;
        }

        $type = XsdType::create($matches['typeName'])
            ->withBaseType('anyType');

        if ($memberTypes = $matches['memberTypes'] ?? '') {
            $type = $type->withMemberTypes(explode(',', $memberTypes));
        }

        return $type;
    }
}
