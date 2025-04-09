<?php

namespace IPP\Student\Classes;

class Send extends Node {
    public string $selector;
    public Expr $expr;
    /** @var Arg[] */
    public array $arguments;

    public function __construct(string $selector, Expr $expr, array $arguments = []) {
        $this->selector = $selector;
        $this->expr = $expr;
        $this->arguments = $arguments;
    }

    public function addArgument(Arg $arg): void {
        $this->arguments[] = $arg;
    }

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Send (selector: {$this->selector}):\n";
        echo $indent . "  Receiver: (Expression)\n";
        $this->expr->print($indentLevel + 2);
        if (!empty($this->arguments)) {
            echo $indent . "  Arguments:\n";
            foreach ($this->arguments as $arg) {
                $arg->print($indentLevel + 2);
            }
        }
    }
}