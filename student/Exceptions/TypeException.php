<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class TypeException extends IPPException
{
    public function __construct(string $message = "Invalid operand types", ?Throwable $previous = null)
    {
        parent::__construct("Invalid operand types\n$message\n", ReturnCode::INTERPRET_TYPE_ERROR, $previous);
    }
}