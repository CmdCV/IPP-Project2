<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;

class Method implements Node {
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
    public function __construct(string $selector, Block $block) {
        $this->selector = $selector;
        $this->block = $block;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Method: {$this->selector}\n";
        $this->block->print($indentLevel + 1);
    }
    public static function fromXML(DOMElement $node): self {
        $selector = $node->getAttribute('selector');
        $blockElement = $node->getElementsByTagName('block')->item(0);

        if (!$blockElement instanceof DOMElement) {
            throw new FileStructureException("Missing <block> element in method '{$this->selector}'");
        }

        $block = Block::fromXML($blockElement);
        return new self($selector, $block);
    }
}