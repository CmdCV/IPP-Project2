<?php

namespace IPP\Student\Classes;

class Literal extends Node {
    public string $classType;
    public $value;

    public function __construct(string $classType, $value) {
        $this->classType = $classType;
        $this->value = $value;
    }

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Literal (class: {$this->classType}, value: {$this->value})\n";
    }
}