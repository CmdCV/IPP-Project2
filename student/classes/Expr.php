<?php

namespace IPP\Student\Classes;

class Expr extends Node
{
    // Pro zjednodušení lze výraz reprezentovat jako jednu z několika možností:
    public ?Literal $literal;
    public ?Send $send;
    public ?Block $block;
    public ?VarNode $var;

    public function __construct(?Literal $literal = null, ?Send $send = null, ?Block $block = null, ?VarNode $var = null)
    {
        $this->literal = $literal;
        $this->send = $send;
        $this->block = $block;
        $this->var = $var;
    }

    public function print(int $indentLevel = 0): void
    {
        $indent = str_repeat('  ', $indentLevel);
        if ($this->literal !== null) {
            $this->literal->print($indentLevel);
        }
        if ($this->send !== null) {
            $this->send->print($indentLevel);
        }
        if ($this->var !== null) {
            $this->var->print($indentLevel);
        }
        if ($this->block !== null) {
            $this->block->print($indentLevel);
        }
        if ($this->literal === null && $this->send === null && $this->var === null && $this->block === null) {
            echo $indent . "Empty expression\n";
        }
    }
}