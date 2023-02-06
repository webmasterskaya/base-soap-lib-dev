<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

class ConstructorAssemblerOptions
{
    /**
     * @var bool
     */
    private $typeHints = false;

    /**
     * @var bool
     */
    private $docBlocks = true;

    /**
     * @var bool
     */
    private $nullableParams = false;

    /**
     * @return ConstructorAssemblerOptions
     */
    public static function create(): ConstructorAssemblerOptions
    {
        return new self();
    }

    /**
     * @param bool $withTypeHints
     *
     * @return ConstructorAssemblerOptions
     */
    public function withTypeHints(bool $withTypeHints = true): ConstructorAssemblerOptions
    {
        $new = clone $this;
        $new->typeHints = $withTypeHints;

        return $new;
    }

    /**
     * @return bool
     */
    public function useTypeHints(): bool
    {
        return $this->typeHints;
    }

    /**
     * @param bool $withDocBlocks
     *
     * @return ConstructorAssemblerOptions
     */
    public function withDocBlocks(bool $withDocBlocks = true): ConstructorAssemblerOptions
    {
        $new = clone $this;
        $new->docBlocks = $withDocBlocks;

        return $new;
    }

    /**
     * @return bool
     */
    public function useDocBlocks(): bool
    {
        return $this->docBlocks;
    }

    /**
     * @param bool $withTypeHints
     *
     * @return ConstructorAssemblerOptions
     */
    public function withNullableParams(bool $withNullableParams = true): ConstructorAssemblerOptions
    {
        $new = clone $this;
        $new->nullableParams = $withNullableParams;

        return $new;
    }

    /**
     * @return bool
     */
    public function useNullableParams(): bool
    {
        return $this->nullableParams;
    }
}