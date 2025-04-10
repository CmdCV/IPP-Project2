<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;

class Assign implements Node {
    private int $order;
    private VarNode $var;
    private Expr $expr;

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
    public static function fromXML(DOMElement $node): self {
        $order = (int)$node->getAttribute('order');
        $varNode = null;
        $exprNode = null;

        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            switch ($child->nodeName) {
                case 'var':
                    $varNode = new VarNode($child->getAttribute('name'));
                    break;
                case 'expr':
                    $exprNode = Expr::fromXML($child);
                    break;
            }
        }

        if ($varNode === null || $exprNode === null) {
            throw new FileStructureException("Incomplete <assign>: missing <var> or <expr>");
        }

        return new self($order, $varNode, $exprNode);
    }
}