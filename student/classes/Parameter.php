<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use LogicException;

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

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        return $pad . "Parameter: order $this->order name $this->name\n";
    }

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        throw new LogicException("Parameter node should never be executed directly.");
    }
}
