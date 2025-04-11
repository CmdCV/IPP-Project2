<?php

namespace IPP\Student\Classes;

use DOMDocument;

class XMLParser
{
    private DOMDocument $source;

    public function __construct(DOMDocument $source)
    {
        $this->source = $source;
    }

    public function parseProgram(): Program
    {
        return Program::fromXML($this->source->documentElement);
    }
}