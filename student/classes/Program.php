<?php

namespace IPP\Student\Classes;

// Třída reprezentující kořenový element <program>
class Program extends Node {
    public string $language;
    public string $description;
    /** @var SolClass[] */
    public array $classes;

    public function __construct(string $language, string $description, array $classes = []) {
        $this->language = $language;
        $this->description = $description;
        $this->classes = $classes;
    }

    public function addClass(SolClass $class) {
        $this->classes[] = $class;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Program:\n";
        echo $indent . "  Language: {$this->language}\n";
        echo $indent . "  Description: {$this->description}\n";
        echo $indent . "  Classes:\n";
        foreach ($this->classes as $class) {
            $class->print($indentLevel + 2);
        }
    }
}