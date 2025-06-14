<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class FileStructureException extends IPPException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct("Invalid XML\n$message\n", ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR, $previous, false);
    }
}