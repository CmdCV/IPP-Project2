<?php

namespace IPP\Student\Classes;

use DOMDocument;
use IPP\Student\Exceptions\FileStructureException;

class XMLParser
{
    private DOMDocument $source;

    public function __construct(DOMDocument $source)
    {
        $this->source = $source;
    }

    /**
     * @throws FileStructureException
     */
    public function parseProgram(): Program
    {
        if($this->source->documentElement!==null) {
            return Program::fromXML($this->source->documentElement);
        }
        throw new FileStructureException("DocumentElement is Null");
    }
}