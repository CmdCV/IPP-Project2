<?php

namespace IPP\Student\Classes;

class Method extends Node {
    public string $selector;
    public Block $block;

    public function __construct(string $selector, Block $block) {
        $this->selector = $selector;
        $this->block = $block;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Method: {$this->selector}\n";
        $this->block->print($indentLevel + 1);
    }
}