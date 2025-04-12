<?php

namespace IPP\Student\RunTime;

use IPP\Student\Exceptions\ValueException;

class ObjectFrame
{
    public function __construct(
        private array $variables = []
    ) {}

    /**
     * @throws ValueException
     */
    public function get(string $name): ObjectInstance
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new ValueException("Undefined variable: $name");
        }
        return $this->variables[$name];
    }

    public function set(string $name, ObjectInstance $value): void
    {
        $this->variables[$name] = $value;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->variables);
    }
}