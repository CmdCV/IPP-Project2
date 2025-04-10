<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class ValueException extends IPPException
{
    public function __construct(string $message = "Invalid value operation", ?Throwable $previous = null)
    {
        parent::__construct("Invalid value operation\n$message\n", ReturnCode::INTERPRET_VALUE_ERROR, $previous);
    }
}