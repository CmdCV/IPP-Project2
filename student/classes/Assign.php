<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;

class Assign extends Node
{
    private int $order;
    private VarNode $var;
    private Expr $expr;

    public function __construct(int $order, VarNode $var, Expr $expr)
    {
        $this->order = $order;
        $this->var = $var;
        $this->expr = $expr;
    }

    public static function fromXML(DOMElement $node): self
    {
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

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getVar(): VarNode
    {
        return $this->var;
    }

    public function getExpr(): Expr
    {
        return $this->expr;
    }
}