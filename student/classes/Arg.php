<?php

namespace IPP\Student\Classes;

class Arg extends Node {
    public int $order;
    public Expr $expr;

    public function __construct(int $order, Expr $expr) {
        $this->order = $order;
        $this->expr = $expr;
    }

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Argument (order: {$this->order}):\n";
        echo $indent . "  Expression:\n";
        $this->expr->print($indentLevel + 2);
    }
}