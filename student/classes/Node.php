<?php

namespace IPP\Student\Classes;

use DOMElement;

interface Node extends Parsable {
    public function print(int $indentLevel = 0): void;
}
?>