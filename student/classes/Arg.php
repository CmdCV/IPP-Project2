<?php

namespace IPP\Student\Classes;

use DOMElement;

class Arg implements Node {
    private int $order;
    private Expr $expr;

    public function getOrder(): int
    {
        return $this->order;
    }
    public function getExpr(): Expr
    {
        return $this->expr;
    }
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

    public static function fromXML(DOMElement $node): self {
        $order = (int)$node->getAttribute('order');
        $expr = null;

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === 'expr') {
                $expr = Expr::fromXML($child);
                break;
            }
        }

        return new self($order, $expr);
    }
}