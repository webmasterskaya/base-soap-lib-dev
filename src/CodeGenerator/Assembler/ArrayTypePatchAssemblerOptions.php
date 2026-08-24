<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Assembler\FluentSetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Assembler\GetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Assembler\ImmutableSetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Assembler\SetterAssemblerOptions;

class ArrayTypePatchAssemblerOptions
{
    private $arrayAccessPatch = true;

    private $countablePatch = true;

    private $fluentSetterOptions = null;

    private $fluentSetterPatch = false;

    private $getterOptions = null;
    private $getterPatch = false;

    private $immutableSetterOptions = null;

    private $immutableSetterPatch = false;

    private $iteratorPatch = true;

    private $setterOptions = null;

    private $setterPatch = false;

    public static function create(): ArrayTypePatchAssemblerOptions
    {
        return new self();
    }

    public function getFluentSetterOptions(): ?FluentSetterAssemblerOptions
    {
        return $this->fluentSetterOptions;
    }

    public function getGetterOptions(): ?GetterAssemblerOptions
    {
        return $this->getterOptions;
    }

    /**
     * @return GetterAssemblerOptions|null
     */
    public function getImmutableSetterOptions(): ?ImmutableSetterAssemblerOptions
    {
        return $this->immutableSetterOptions;
    }

    public function getSetterOptions(): ?SetterAssemblerOptions
    {
        return $this->setterOptions;
    }

    public function useArrayAccessPatch(): bool
    {
        return $this->arrayAccessPatch;
    }

    public function useCountablePatch(): bool
    {
        return $this->countablePatch;
    }

    public function useFluentSetterPatch(): bool
    {
        return $this->fluentSetterPatch;
    }

    public function useGetterPatch(): bool
    {
        return $this->getterPatch;
    }

    public function useImmutableSetterPatch(): bool
    {
        return $this->immutableSetterPatch;
    }

    public function useIteratorPatch(): bool
    {
        return $this->iteratorPatch;
    }

    public function useSetterPatch(): bool
    {
        return $this->setterPatch;
    }

    public function withArrayAccessPatch(bool $arrayAccessPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->arrayAccessPatch = $arrayAccessPatch;
        return $new;
    }

    public function withCountablePatch(bool $countablePatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->countablePatch = $countablePatch;
        return $new;
    }

    public function withFluentSetterOptions(
        FluentSetterAssemblerOptions $options = null,
    ): ArrayTypePatchAssemblerOptions {
        $new = clone $this;
        $new->fluentSetterOptions = $options ?? new FluentSetterAssemblerOptions();
        return $new;
    }

    public function withFluentSetterPatch(bool $fluentSetterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->fluentSetterPatch = $fluentSetterPatch;
        return $new;
    }

    public function withGetterOptions(GetterAssemblerOptions $options = null): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->getterOptions = $options ?? new GetterAssemblerOptions();
        return $new;
    }

    public function withGetterPatch(bool $getterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->getterPatch = $getterPatch;
        return $new;
    }

    public function withImmutableSetterOptions(
        ImmutableSetterAssemblerOptions $options = null,
    ): ArrayTypePatchAssemblerOptions {
        $new = clone $this;
        $new->immutableSetterOptions = $options ?? new ImmutableSetterAssemblerOptions();
        return $new;
    }

    public function withImmutableSetterPatch(bool $immutableSetterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->immutableSetterPatch = $immutableSetterPatch;
        return $new;
    }

    public function withIteratorPatch(bool $iteratorPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->iteratorPatch = $iteratorPatch;
        return $new;
    }

    public function withSetterOptions(SetterAssemblerOptions $options = null): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->setterOptions = $options ?? new SetterAssemblerOptions();
        return $new;
    }

    public function withSetterPatch(bool $setterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->setterPatch = $setterPatch;
        return $new;
    }
}
