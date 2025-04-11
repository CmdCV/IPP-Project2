<?php

namespace IPP\Student\Classes;

use DOMElement;

class Parameter extends Node
{
    private int $order;
    private string $name;

    public function __construct(int $order, string $name)
    {
        $this->order = $order;
        $this->name = $name;
    }

    public static function fromXML(DOMElement $node): self
    {
        return new self(
            (int)$node->getAttribute('order'),
            $node->getAttribute('name')
        );
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function print($indentLevel = 0): void
    {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Parameter: order {$this->order} name {$this->name}\n";
    }
}