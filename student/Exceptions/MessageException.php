<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class MessageException extends IPPException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct("Message not understood\n$message\n", ReturnCode::INTERPRET_DNU_ERROR, $previous);
    }
}