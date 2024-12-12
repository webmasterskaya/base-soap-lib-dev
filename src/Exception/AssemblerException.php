<?php

namespace Webmasterskaya\Soap\Base\Dev\Exception;

class AssemblerException extends RuntimeException
{
    /**
     * @param \Exception $e
     *
     * @return \Webmasterskaya\Soap\Base\Dev\Exception\AssemblerException
     */
    public static function fromException(\Exception $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}