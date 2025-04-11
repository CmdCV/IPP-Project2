<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\MessageException;

class SolClass implements Node {
    private string $name;
    private string $parent;
    /** @var Method[] */
    private array $methods;

    public function getName(): string
    {
        return $this->name;
    }

    public function getParent(): string
    {
        // IDEA: Find a parent class object by name and return it instead of just name string
        return $this->parent;
    }

    public function getMethods(): array
    {
        //idea: Also search for methods of parents if not found in this object
        return $this->methods;
    }

    public function __construct(string $name, string $parent, array $methods = []) {
        $this->name = $name;
        $this->parent = $parent;
        $this->methods = $methods;
    }

    public function addMethod(Method $method): void {
        $this->methods[] = $method;
    }

    public function findMethodBySelector(string $selector): Method {
        foreach ($this->methods as $method) {
            if ($method->getSelector() === $selector) {
                return $method;
            }
        }
        throw new MessageException("Method '{$selector}' not found in class '{$this->name}'");
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