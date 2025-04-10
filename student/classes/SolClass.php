<?php

namespace IPP\Student\Classes;

use DOMElement;

class SolClass implements Node {
    private string $name;
    private string $parent;
    /** @var Method[] */
    private array $methods;

    public function __construct(string $name, string $parent, array $methods = []) {
        $this->name = $name;
        $this->parent = $parent;
        $this->methods = $methods;
    }

    public function addMethod(Method $method): void {
        $this->methods[] = $method;
    }

    public function print($indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Class: {$this->name} (parent: {$this->parent})\n";
        echo $indent . "  Methods:\n";
        foreach ($this->methods as $method) {
            $method->print($indentLevel + 2);
        }
    }
    public static function fromXML(DOMElement $node): self {
        $name = $node->getAttribute('name');
        $parent = $node->getAttribute('parent');
        $solClass = new self($name, $parent);

        foreach ($node->getElementsByTagName('method') as $methodNode) {
            if ($methodNode instanceof DOMElement) {
                $solClass->addMethod(Method::fromXML($methodNode));
            }
        }

        return $solClass;
    }
}