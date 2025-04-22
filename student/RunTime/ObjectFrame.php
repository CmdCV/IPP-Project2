<?php

namespace IPP\Student\RunTime;

use IPP\Student\Exceptions\TypeException;

class ObjectFrame
{
    /**
     * @param array<string, ObjectInstance> $variables
     */
    public function __construct(array $variables = []) {
        $this->variables = $variables;
    }
    /** @var array<string, ObjectInstance> */
    private array $variables;

    /**
     * @throws TypeException
     */
    public function get(string $name): ObjectInstance
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new TypeException("Undefined variable: $name");
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
