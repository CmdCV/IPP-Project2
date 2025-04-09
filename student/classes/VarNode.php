<?php

namespace IPP\Student\Classes;

// Třída reprezentující element <var>
class VarNode extends Node {
    public string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Variable: {$this->name}\n";
    }
}