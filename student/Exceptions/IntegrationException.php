<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class IntegrationException extends IPPException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct("Integration error\n$message\n", ReturnCode::INTEGRATION_ERROR, $previous);
    }
}