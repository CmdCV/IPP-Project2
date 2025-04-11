<?php

namespace IPP\Student\Classes;

use DOMElement;

class Literal extends Node
{
    private string $classType;
    private $value;

    public function __construct(string $classType, $value)
    {
        $this->classType = $classType;
        $this->value = $value;
    }

    public static function fromXML(DOMElement $node): self
    {
        return new self(
            $node->getAttribute('class'),
            $node->getAttribute('value')
        );
    }

    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        return "{$pad}Literal(classType={$this->classType}, value={$this->value})\n";
    }
}