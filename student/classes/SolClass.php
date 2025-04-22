<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use LogicException;

class SolClass extends Node
{
    private string $name;
    private string $parentName;
    private ?SolClass $parent = null;

    /** @var array<Method> */
    private array $methods;

    /**
     * @param array<Method> $methods
     */
    public function __construct(string $name, string $parentName, array $methods = [])
    {
        $this->name = $name;
        $this->parentName = $parentName;
        $this->methods = $methods;
    }

    /**
     * @throws FileStructureException
     */
    public static function fromXML(DOMElement $node): self
    {
        $name = $node->getAttribute('name');
        $parent = $node->getAttribute('parent');
        $solClass = new self($name, $parent);

        foreach ($node->getElementsByTagName('method') as $methodNode) {
            $solClass->addMethod(Method::fromXML($methodNode));
        }

        return $solClass;
    }

    /**
     * @param array<string, SolClass> $allClasses
     * @throws ValueException
     */
    public function linkParent(array $allClasses): void
    {
        if ($this->parentName === '' || $this->parentName === $this->name) {
            $this->parent = null; // root třída – nemá rodiče
            return;
        }

        if (!isset($allClasses[$this->parentName])) {
            throw new ValueException("Unknown parent class '$this->parentName' for class '$this->name'");
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

    /**
     * @return array<Method>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function findMethod(string $selector): ?Method
    {
        foreach ($this->methods as $method) {
            if ($method->getSelector() === $selector) {
                return $method;
            }
        }
        return $this->parent?->findMethod($selector);
    }

    public function instantiate(): ObjectInstance
    {
        $instance = new ObjectInstance($this);

        if ($this->name === 'Integer') {
            $instance->setAttribute('__value', 0);
        }
        if ($this->name === 'String') {
            $instance->setAttribute('__value', "");
        }

        return $instance;
    }

    /**
     * @throws ValueException
     */
    public function instantiateFrom(ObjectInstance $source): ObjectInstance
    {
        if (!$source->isAncestorOf($this)) {
            throw new ValueException("Incompatible class in from: {$source->getClass()->getName()} is not instance of {$this->getName()}");
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

    public function isSubclassOf(string $ancestor): bool
    {
        $current = $this;
        while ($current !== null) {
            if ($current->getName() === $ancestor) {
                return true;
            }
            $current = $current->getParent();
        }
        return false;
    }
}
