<?php

namespace Webmasterskaya\Soap\Base\Dev\Exception;

use Exception;

class AssemblerException extends RuntimeException
{
    public static function fromException(Exception $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
