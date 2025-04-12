<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\BlockInstance;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use LogicException;

class Expr extends Node
{
    private ?Literal $literal;
    private ?Send $send;
    private ?Block $block;
    private ?VarNode $var;

    public function getLiteral(): ?Literal
    {
        return $this->literal;
    }

    public function getSend(): ?Send
    {
        return $this->send;
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function getVar(): ?VarNode
    {
        return $this->var;
    }

    public function __construct(?Literal $literal = null, ?Send $send = null, ?Block $block = null, ?VarNode $var = null)
    {
        $this->literal = $literal;
        $this->send = $send;
        $this->block = $block;
        $this->var = $var;
    }

    /**
     * @throws FileStructureException
     */
    public static function fromXML(DOMElement $node): self
    {
        $literal = null;
        $send = null;
        $block = null;
        $var = null;

        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            switch ($child->nodeName) {
                case 'literal':
                    $literal = Literal::fromXML($child);
                    break;
                case 'send':
                    $send = Send::fromXML($child);
                    break;
                case 'block':
                    $block = Block::fromXML($child);
                    break;
                case 'var':
                    $var = new VarNode($child->getAttribute('name'));
                    break;
                default:
                    throw new FileStructureException("Unknown child in <expr>: <$child->nodeName> not supported.");
            }
        }

        return new self($literal, $send, $block, $var);
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        if ($this->literal !== null) {
            return $this->literal->execute($self, $frame);
        }
        if ($this->send !== null) {
            return $this->send->execute($self, $frame);
        }
        if ($this->block !== null) {
            return new BlockInstance($this->block, $self);
        }
        if ($this->var !== null) {
            return $this->var->execute($self, $frame);
        }

        throw new LogicException("Invalid expression: none of the expression types is set.");
    }
}
