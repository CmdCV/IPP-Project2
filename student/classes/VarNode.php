<?php

namespace IPP\Student\Classes;

use DOMElement;

class VarNode implements Node {
    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Variable: {$this->name}\n";
    }

    public static function fromXML(DOMElement $node): self {
        return new self($node->getAttribute('name'));
    }
}