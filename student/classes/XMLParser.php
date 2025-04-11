<?php

namespace IPP\Student\Classes;

use DOMDocument;
use DOMElement;

class XMLParser {
    private DOMDocument $source;

    public function __construct(DOMDocument $source) {
        $this->source = $source;
    }
    public function parseProgram(): Program {
        return Program::fromXML($this->source->documentElement);
    }
}