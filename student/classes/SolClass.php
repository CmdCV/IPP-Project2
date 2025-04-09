<?php

namespace IPP\Student\Classes;

// Protože "class" je rezervované slovo, používáme název SolClass
class SolClass extends Node {
    public string $name;
    public string $parent;
    /** @var Method[] */
    public array $methods;

    public function __construct(string $name, string $parent, array $methods = []) {
        $this->name = $name;
        $this->parent = $parent;
        $this->methods = $methods;
    }

    public function addMethod(Method $method): void {
        $this->methods[] = $method;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Class: {$this->name} (parent: {$this->parent})\n";
        echo $indent . "  Methods:\n";
        foreach ($this->methods as $method) {
            $method->print($indentLevel + 2);
        }
    }
}