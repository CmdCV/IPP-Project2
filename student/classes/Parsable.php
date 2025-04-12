<?php

namespace IPP\Student\Classes;

use DOMElement;

interface Parsable
{
    public static function fromXML(DOMElement $node): self;
}