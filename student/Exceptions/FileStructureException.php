<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class FileStructureException extends IPPException
{

    /**
     * @param string $string
     */
    public function __construct(string $string, ?Throwable $previous = null)
    {
        parent::__construct("Invalid XML\n$string\n", ReturnCode::INVALID_SOURCE_STRUCTURE_ERROR, $previous, false);
    }
}