<?php

namespace IPP\Student\Runtime;

class Frame {
    private array $variables = [];

    public function set(string $name, mixed $value): void {
        $this->variables[$name] = $value;
    }

    public function get(string $name): mixed {
        if (!array_key_exists($name, $this->variables)) {
            throw new \RuntimeException("Undefined variable '$name'");
        }
        return $this->variables[$name];
    }

    public function has(string $name): bool {
        return array_key_exists($name, $this->variables);
    }
}