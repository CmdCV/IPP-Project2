<?php

namespace IPP\Student\Runtime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Classes\Method;
use IPP\Student\Exceptions\MessageException;

class ObjectInstance {
    public function __construct(
        private SolClass $class,
        private array $attributes = [] // name => Value
    ) {}

    public function getClass(): SolClass {
        return $this->class;
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): Value {
        if (!array_key_exists($name, $this->attributes)) {
            throw new MessageException("Undefined attribute '{$name}' in object of class '{$this->class->getName()}'");
        }
        return $this->attributes[$name];
    }

    public function setAttribute(string $name, Value $value): void {
        $this->attributes[$name] = $value;
    }

    public function hasAttribute(string $name): bool {
        return array_key_exists($name, $this->attributes);
    }

    public function findMethod(string $selector): Method {
        return $this->class->findMethodBySelector($selector);
    }
}
