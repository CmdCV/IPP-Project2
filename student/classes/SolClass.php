<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use LogicException;

class SolClass extends Node
{
    private string $name;
    private string $parentName;
    private ?SolClass $parent = null;

    /** @var Method[] */
    private array $methods;

    public function __construct(string $name, string $parentName, array $methods = [])
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->methods = $methods;
    }

    public static function fromXML(DOMElement $node): self
    {
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

    public function linkParent(array $allClasses): void
    {
        if ($this->parentName === '' || $this->parentName === $this->name) {
            $this->parent = null; // root třída – nemá rodiče
            return;
        }

        if (!isset($allClasses[$this->parentName])) {
            throw new ValueException("Unknown parent class '{$this->parentName}' for class '{$this->name}'");
        }

        $this->parent = $allClasses[$this->parentName];
    }

    public function addMethod(Method $method): void
    {
        $this->methods[] = $method;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParent(): ?SolClass
    {
        return $this->parent;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function findMethod(string $selector, int $arity): ?Method
    {
        foreach ($this->methods as $method) {
            if ($method->getSelector() === $selector && $method->getBlock()->getArity() === $arity) {
                return $method;
            }
        }
        return $this->parent?->findMethod($selector, $arity);
    }

    public function instantiate(): ObjectInstance
    {
        $instance = new ObjectInstance($this);

        // Výchozí hodnota pro Integer
        if ($this->name === 'Integer') {
            $instance->setAttribute('__value', 0);
        }
        if ($this->name === 'String') {
            $instance->setAttribute('__value', "");
        }

        return $instance;
    }

    public function instantiateFrom(ObjectInstance $source): ObjectInstance
    {
        if (!$source->isInstanceOf($this)) {
            throw new MessageException("Incompatible class in from:");
        }

        $new = $this->instantiate();
        foreach ($source->getAllAttributes() as $name => $value) {
            $new->setAttribute($name, $value);
        }
        return $new;
    }

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        throw new LogicException("Class definition cannot be executed as a runtime node.");
    }
}
