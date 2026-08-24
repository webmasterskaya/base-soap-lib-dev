<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Assembler;

class ConstructorAssemblerOptions
{
    /**
     * @var bool
     */
    private $docBlocks = true;

    /**
     * @var bool
     */
    private $nullableParams = false;
    /**
     * @var bool
     */
    private $typeHints = false;

    public static function create(): ConstructorAssemblerOptions
    {
        return new self();
    }

    public function useDocBlocks(): bool
    {
        return $this->docBlocks;
    }

    public function useNullableParams(): bool
    {
        return $this->nullableParams;
    }

    public function useTypeHints(): bool
    {
        return $this->typeHints;
    }

    public function withDocBlocks(bool $withDocBlocks = true): ConstructorAssemblerOptions
    {
        $new = clone $this;
        $new->docBlocks = $withDocBlocks;

        return $new;
    }

    public function withNullableParams(bool $withNullableParams = true): ConstructorAssemblerOptions
    {
        $new = clone $this;
        $new->nullableParams = $withNullableParams;

        return $new;
    }

    public function withTypeHints(bool $withTypeHints = true): ConstructorAssemblerOptions
    {
        $new = clone $this;
        $new->typeHints = $withTypeHints;

        return $new;
    }
}
