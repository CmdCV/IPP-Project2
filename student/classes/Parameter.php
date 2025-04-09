<?php

namespace IPP\Student\Classes;

class Parameter extends Node {
    public int $order;
    public string $name;

    public function __construct(int $order, string $name) {
        $this->order = $order;
        $this->name = $name;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Parameter: order {$this->order} name {$this->name}\n";
    }
}