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
    private string|int|bool|null $value;

    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getValue(): string|int|bool|null
    {
        return $this->value;
    }

    public function __construct(string $classType, string|int|bool|null $value)
    {
        $this->classType = $classType;
        $this->value = $value;
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        $value = is_scalar($this->value) ? (string)$this->value : 'null';
        return $pad . "Literal(classType=$this->classType, value=$value)\n";
    }

    public static function fromXML(DOMElement $node): self
    {
        $value = $node->getAttribute('value');
        $parsedValue = match ($node->getAttribute('class')) {
            'Integer' => (int)$value,
            'True' => true,
            'False' => false,
            'Nil' => null,
            default => $value
        };

        return new self(
            $node->getAttribute('class'),
            $parsedValue
        );
    }

    /**
     * @throws ValueException
     */
    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        return match ($this->classType) {
            'Integer' => ObjectFactory::integer((int)$this->value),
            'String' => ObjectFactory::string((string)$this->value),
            'True' => ObjectFactory::true(),
            'False' => ObjectFactory::false(),
            'Nil' => ObjectFactory::nil(),
            'class' => ObjectFactory::classReference((string)$this->value),
            default => throw new ValueException("Unknown literal class: $this->classType")
        };
    }
}
