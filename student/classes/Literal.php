<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFactory;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;

class Literal extends Node
{
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

    public function __construct(string $classType, $value)
    {
        $this->classType = $classType;
        $this->value = $value;
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        return "{$pad}Literal(classType={$this->classType}, value={$this->value})\n";
    }

    public static function fromXML(DOMElement $node): self
    {
        return new self(
            $node->getAttribute('class'),
            $node->getAttribute('value')
        );
    }

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        return match ($this->classType) {
            'Integer' => ObjectFactory::integer((int)$this->value),
            'String' => ObjectFactory::string($this->value),
            'True' => ObjectFactory::true(),
            'False' => ObjectFactory::false(),
            'Nil' => ObjectFactory::nil(),
            'class' => ObjectFactory::classReference($this->value), // např. String.from:
            default => throw new ValueException("Unknown literal class: {$this->classType}")
        };
    }
}
