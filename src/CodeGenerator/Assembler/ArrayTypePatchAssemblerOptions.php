<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

use Phpro\SoapClient\CodeGenerator\Assembler\FluentSetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Assembler\GetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Assembler\ImmutableSetterAssemblerOptions;
use Phpro\SoapClient\CodeGenerator\Assembler\SetterAssemblerOptions;

class ArrayTypePatchAssemblerOptions
{
    private $getterPatch = false;

    private $getterOptions = null;

    private $setterPatch = false;

    private $setterOptions = null;

    private $immutableSetterPatch = false;

    private $immutableSetterOptions = null;

    private $fluentSetterPatch = false;

    private $fluentSetterOptions = null;

    private $iteratorPatch = true;

    private $countablePatch = true;

    private $arrayAccessPatch = true;

    public static function create(): ArrayTypePatchAssemblerOptions
    {
        return new self();
    }

    /**
     * @return bool
     */
    public function useGetterPatch(): bool
    {
        return $this->getterPatch;
    }

    /**
     * @param bool $getterPatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withGetterPatch(bool $getterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->getterPatch = $getterPatch;
        return $new;
    }

    /**
     * @return GetterAssemblerOptions|null
     */
    public function getGetterOptions(): ?GetterAssemblerOptions
    {
        return $this->getterOptions;
    }

    /**
     * @param GetterAssemblerOptions|null $options
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withGetterOptions(GetterAssemblerOptions $options = null): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->getterOptions = $options ?? new GetterAssemblerOptions();
        return $new;
    }

    /**
     * @return bool
     */
    public function useSetterPatch(): bool
    {
        return $this->setterPatch;
    }

    /**
     * @param bool $setterPatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withSetterPatch(bool $setterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->setterPatch = $setterPatch;
        return $new;
    }

    /**
     * @return SetterAssemblerOptions|null
     */
    public function getSetterOptions(): ?SetterAssemblerOptions
    {
        return $this->setterOptions;
    }

    /**
     * @param SetterAssemblerOptions|null $options
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withSetterOptions(SetterAssemblerOptions $options = null): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->setterOptions = $options ?? new SetterAssemblerOptions();
        return $new;
    }

    /**
     * @return bool
     */
    public function useImmutableSetterPatch(): bool
    {
        return $this->immutableSetterPatch;
    }

    /**
     * @param bool $immutableSetterPatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withImmutableSetterPatch(bool $immutableSetterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->immutableSetterPatch = $immutableSetterPatch;
        return $new;
    }

    /**
     * @return GetterAssemblerOptions|null
     */
    public function getImmutableSetterOptions(): ?ImmutableSetterAssemblerOptions
    {
        return $this->immutableSetterOptions;
    }

    /**
     * @param ImmutableSetterAssemblerOptions|null $options
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withImmutableSetterOptions(ImmutableSetterAssemblerOptions $options = null
    ): ArrayTypePatchAssemblerOptions {
        $new = clone $this;
        $new->immutableSetterOptions = $options ?? new ImmutableSetterAssemblerOptions();
        return $new;
    }

    /**
     * @return bool
     */
    public function useFluentSetterPatch(): bool
    {
        return $this->fluentSetterPatch;
    }

    /**
     * @param bool $fluentSetterPatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withFluentSetterPatch(bool $fluentSetterPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->fluentSetterPatch = $fluentSetterPatch;
        return $new;
    }

    /**
     * @return FluentSetterAssemblerOptions|null
     */
    public function getFluentSetterOptions(): ?FluentSetterAssemblerOptions
    {
        return $this->fluentSetterOptions;
    }

    /**
     * @param FluentSetterAssemblerOptions|null $options
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withFluentSetterOptions(FluentSetterAssemblerOptions $options = null
    ): ArrayTypePatchAssemblerOptions {
        $new = clone $this;
        $new->fluentSetterOptions = $options ?? new FluentSetterAssemblerOptions();
        return $new;
    }

    /**
     * @return bool
     */
    public function useIteratorPatch(): bool
    {
        return $this->iteratorPatch;
    }

    /**
     * @param bool $iteratorPatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withIteratorPatch(bool $iteratorPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->iteratorPatch = $iteratorPatch;
        return $new;
    }

    /**
     * @return bool
     */
    public function useCountablePatch(): bool
    {
        return $this->countablePatch;
    }

    /**
     * @param bool $countablePatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withCountablePatch(bool $countablePatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->countablePatch = $countablePatch;
        return $new;
    }

    /**
     * @return bool
     */
    public function useArrayAccessPatch(): bool
    {
        return $this->arrayAccessPatch;
    }

    /**
     * @param bool $arrayAccessPatch
     * @return ArrayTypePatchAssemblerOptions
     */
    public function withArrayAccessPatch(bool $arrayAccessPatch = true): ArrayTypePatchAssemblerOptions
    {
        $new = clone $this;
        $new->arrayAccessPatch = $arrayAccessPatch;
        return $new;
    }
}