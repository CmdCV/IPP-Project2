<?php

namespace IPP\Student\Classes;

use DOMElement;

class VarNode extends Node
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function fromXML(DOMElement $node): self
    {
        return new self($node->getAttribute('name'));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        return "{$pad}Var(name={$this->name})\n";
    }
}