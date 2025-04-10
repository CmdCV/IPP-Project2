<?php

namespace IPP\Student\Classes;

use DOMElement;

class Block implements Node
{
    private int $arity;
    /** @var Assign[] */
    private array $assignments;
    /** @var Parameter[] */
    private array $parameters;
    /** @var Expr[] */
    private array $expressions;

    public function __construct(int $arity, array $assignments = [], array $parameters = [], array $expressions = [])
    {
        $this->arity = $arity;
        $this->assignments = $assignments;
        $this->parameters = $parameters;
        $this->expressions = $expressions;
    }

    public function addAssignment(Assign $assignment): void
    {
        $this->assignments[] = $assignment;
    }

    public function addParameter(Parameter $parameter): void
    {
        $this->parameters[] = $parameter;
    }

    public function print($indentLevel = 0): void
    {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Block (arity: {$this->arity})\n";
        if (!empty($this->parameters)) {
            echo $indent . "  Parameters:\n";
            foreach ($this->parameters as $param) {
                $param->print($indentLevel + 2);
            }
        }
        if (!empty($this->assignments)) {
            echo $indent . "  Assignments:\n";
            foreach ($this->assignments as $assign) {
                $assign->print($indentLevel + 2);
            }
        }
    }

    public static function fromXML(DOMElement $node): self
    {
        $arity = (int)$node->getAttribute('arity');
        $block = new self($arity);

        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            switch ($child->nodeName) {
                case 'parameter':
                    $block->addParameter(Parameter::fromXML($child));
                    break;
                case 'assign':
                    $block->addAssignment(Assign::fromXML($child));
                    break;
            }
        }

        return $block;
    }
}