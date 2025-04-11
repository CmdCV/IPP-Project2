<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Runtime\Frame;
use IPP\Student\Runtime\FrameStack;
use IPP\Student\Runtime\ObjectInstance;
use IPP\Student\Runtime\Value;
class Expr implements Node
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

    public function evaluate(FrameStack $stack): Value
    {

        if ($this->send !== null) {
            fwrite(STDERR, "[Expr] evaluating send: {$this->send->getSelector()}\n");
            return $this->send->evaluate($stack);
        }

        if ($this->literal !== null) {
            fwrite(STDERR, "[Expr] evaluating literal\n");
            $type = $this->literal->getClassType();
            $value = $this->literal->getValue();

            if ($type === 'class') {
                // Získáme jméno třídy z value a vracíme Value typu "class" s názvem třídy
                return new Value('class', $value);
            }

            if (in_array($type, ['Integer', 'String'])) {
                $instance = new ObjectInstance(new SolClass($type, 'Object'), [
                    'value' => new Value($type, $value),
                ]);
                echo "[Expr] returning Value(Object, ".$instance->getClass()->getName().") for literal \n";
                return new Value('Object', $instance);
            }

            if ($type === 'True') {
                return new Value('Object', $stack->getTrue());
            }
            if ($type === 'False') {
                return new Value('Object', $stack->getFalse());
            }
            if ($type === 'Nil') {
                return new Value('Object', $stack->getNil());
            }

            throw new TypeException("Unknown literal type: $type");
        }

        if ($this->block !== null) {
            $blockObject = new ObjectInstance(new SolClass('Block', 'Object'), [
                'block' => new Value('Block', $this->block),
                'arity' => new Value('Integer', $this->block->getArity()),
            ]);
            return new Value('Object', $blockObject);
        }

        if ($this->var !== null) {
            fwrite(STDERR, "[Expr] evaluating var: {$this->var->getName()}\n");
            return $stack->get($this->var->getName());
        }

        throw new MessageException("Invalid expression – no content found in <expr> node.");
    }

    public static function fromXML(DOMElement $node): self {
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
                    throw new FileStructureException("Unknown child in <expr>: <{$child->nodeName}> not supported.");
            }
        }

        return new self($literal, $send, $block, $var);
    }
}