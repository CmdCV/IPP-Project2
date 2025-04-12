<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFactory;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use IPP\Student\RunTime\SuperReference;

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
        return $pad."Var(name=$this->name)\n";
    }

    /**
     * @throws ValueException
     */
    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        return match ($this->name) {
            'self' => $self,
            'super' => new SuperReference($self),
            'nil' => ObjectFactory::nil(),
            'true' => ObjectFactory::true(),
            'false' => ObjectFactory::false(),
            default => $frame->get($this->name),
        };
    }
}
