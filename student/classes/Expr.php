<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
class Expr implements Node
{
    private ?Literal $literal;
    private ?Send $send;
    private ?Block $block;
    private ?VarNode $var;

    public function __construct(?Literal $literal = null, ?Send $send = null, ?Block $block = null, ?VarNode $var = null)
    {
        $this->literal = $literal;
        $this->send = $send;
        $this->block = $block;
        $this->var = $var;
    }

    public function print(int $indentLevel = 0): void
    {
        $this->literal?->print($indentLevel);
        $this->send?->print($indentLevel);
        $this->var?->print($indentLevel);
        $this->block?->print($indentLevel);

        if ($this->literal === null && $this->send === null && $this->var === null && $this->block === null) {
            $indent = str_repeat('  ', $indentLevel);
            echo $indent . "Empty expression\n";
        }
    }

    public static function fromXML(DOMElement $node): self {
        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            return match ($child->nodeName) {
                'literal' => new self(Literal::fromXML($child)),
                'send' => new self(null, Send::fromXML($child)),
                'block' => new self(null, null, Block::fromXML($child)),
                'var' => new self(null, null, null, new VarNode($child->getAttribute('name'))),
                default => throw new FileStructureException("Unknown child in <expr>: <$child->nodeName ...>"),
            };
        }

        return new self(); // empty expr fallback
    }
}