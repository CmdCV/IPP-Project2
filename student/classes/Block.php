<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFactory;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;

class Block extends Node
{
    private int $arity;
    /** @var Assign[] */
    private array $assignments;
    /** @var Parameter[] */
    private array $parameters;
    /** @var Expr[] */
    private array $expressions;

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

    public function __construct(int $arity, array $assignments = [], array $parameters = [], array $expressions = [])
    {
        $this->arity = $arity;
        usort($assignments, fn($a, $b) => $a->getOrder() <=> $b ->getOrder());
        $this->assignments = $assignments;
        usort($parameters, fn($a, $b) => $a->getOrder() <=> $b ->getOrder());
        $this->parameters = $parameters;
        $this->expressions = $expressions;
    }

    /**
     * @throws FileStructureException
     */
    public static function fromXML(DOMElement $node): self
    {
        $arity = (int)$node->getAttribute('arity');
        $assignments = [];
        $parameters = [];

        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            switch ($child->nodeName) {
                case 'parameter':
                    $parameters[] = Parameter::fromXML($child);
                    break;
                case 'assign':
                    $assignments[] = Assign::fromXML($child);
                    break;
            }
        }

        return new self($arity, $assignments, $parameters);
    }

    /**
     * @throws TypeException
     * @throws ValueException
     * @throws MessageException
     */
    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        $result = ObjectFactory::nil();
        foreach ($this->assignments as $assign) {
            $result = $assign->execute($self, $frame);
        }
        return $result;
    }
}
