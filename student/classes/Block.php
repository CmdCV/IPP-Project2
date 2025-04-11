<?php

namespace IPP\Student\Classes;

use DOMElement;

class Block extends Node
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

    public function addParameter(Parameter $parameter): void
    {
        $this->parameters[] = $parameter;
    }

    public function addAssignment(Assign $assignment): void
    {
        $this->assignments[] = $assignment;
    }

    public function getArity(): int
    {
        return $this->arity;
    }

    public function getAssignments(): array
    {
        return $this->assignments;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getExpressions(): array
    {
        return $this->expressions;
    }
}