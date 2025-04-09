<?php

namespace IPP\Student\Classes;

class Assign extends Node {
    public int $order;
    public VarNode $var;
    public Expr $expr;

    public function __construct(int $order, VarNode $var, Expr $expr) {
        $this->order = $order;
        $this->var = $var;
        $this->expr = $expr;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Assignment: order {$this->order}\n";
        echo $indent . "  Variable:\n";
        $this->var->print($indentLevel + 2);
        echo $indent . "  Expression:\n";
        $this->expr->print($indentLevel + 2);
    }
}