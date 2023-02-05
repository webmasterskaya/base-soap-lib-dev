<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

class ArrayTypePatchAssemblerOptions
{
    private $getterPatch = false;

    private $setterPatch = false;

    private $immutableSetterPatch = false;

    private $fluentSetterPatch = false;

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