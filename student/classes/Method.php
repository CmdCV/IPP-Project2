<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;

class Method extends Node
{
    private string $selector;
    private Block $block;

    public function __construct(string $selector, Block $block)
    {
        $this->selector = $selector;
        $this->block = $block;
    }

    public static function fromXML(DOMElement $node): self
    {
        $selector = $node->getAttribute('selector');
        $blockElement = $node->getElementsByTagName('block')->item(0);

        if (!$blockElement instanceof DOMElement) {
            throw new FileStructureException("Missing <block> element in method '{$selector}'");
        }

        $block = Block::fromXML($blockElement);
        return new self($selector, $block);
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getBlock(): Block
    {
        return $this->block;
    }
}