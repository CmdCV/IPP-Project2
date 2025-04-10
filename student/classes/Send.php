<?php

namespace IPP\Student\Classes;

use DOMElement;

class Send implements Node {
    private string $selector;
    private Expr $expr;
    /** @var Arg[] */
    private array $arguments;

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

    public static function fromXML(DOMElement $node): self {
        $selector = $node->getAttribute('selector');
        $receiver = null;
        $args = [];

        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            switch ($child->nodeName) {
                case 'expr':
                    $receiver = Expr::fromXML($child);
                    break;
                case 'arg':
                    $args[] = Arg::fromXML($child);
                    break;
            }
        }

        return new self($selector, $receiver, $args);
    }
}