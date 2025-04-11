<?php

namespace IPP\Student\Classes;

use DOMElement;

class Literal implements Node {
    private string $classType;
    private $value;

    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getValue()
    {
        return $this->value;
    }
    public function __construct(string $classType, $value) {
        $this->classType = $classType;
        $this->value = $value;
    }

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Literal (class: {$this->classType}, value: {$this->value})\n";
    }

    public static function fromXML(DOMElement $node): self {
        return new self(
            $node->getAttribute('class'),
            $node->getAttribute('value')
        );
    }
}