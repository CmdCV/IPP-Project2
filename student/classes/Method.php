<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use LogicException;

class Method extends Node
{
    private string $selector;
    private Block $block;

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getBlock(): Block
    {
        return $this->block;
    }

    public function __construct(string $selector, Block $block)
    {
        $this->selector = $selector;
        $this->block = $block;
    }

    /**
     * @throws FileStructureException
     */
    public static function fromXML(DOMElement $node): self
    {
        $selector = $node->getAttribute('selector');
        $blockElement = $node->getElementsByTagName('block')->item(0);

        if (!$blockElement instanceof DOMElement) {
            throw new FileStructureException("Missing <block> element in method '$selector'");
        }

        $block = Block::fromXML($blockElement);
        return new self($selector, $block);
    }

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        throw new LogicException("Cannot execute a method directly.");
    }

    /**
     * @param array<ObjectInstance> $args
     * @throws MessageException
     * @throws ValueException
     * @throws TypeException
     */
    public function invoke(ObjectInstance $self, array $args): ObjectInstance
    {
        $parameters = $this->block->getParameters();

        if (count($args) !== count($parameters)) {
            throw new MessageException("Wrong arity in method '$this->selector'");
        }

        $frame = new ObjectFrame();

        foreach ($parameters as $i => $param) {
            $frame->set($param->getName(), $args[$i]);
        }

        return $this->block->execute($self, $frame);
    }
}
