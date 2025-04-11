<?php

namespace IPP\Student\classes;

use DOMElement;

interface Parsable
{
    public static function fromXML(DOMElement $node): self;
}