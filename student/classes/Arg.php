<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;

class Arg extends Node
{
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
    public function __construct(int $order, Expr $expr)
    {
        $this->order = $order;
        $this->expr = $expr;
    }

    /**
     * @throws FileStructureException
     */
    public static function fromXML(DOMElement $node): self
    {
        $order = (int)$node->getAttribute('order');
        $expr = null;

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === 'expr') {
                $expr = Expr::fromXML($child);
                break;
            }
        }
        if($expr === null) {
            throw new FileStructureException('Missing expr element in arg node');
        }
        return new self($order, $expr);
    }

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        return $this->expr->execute($self, $frame);
    }
}
