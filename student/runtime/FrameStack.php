<?php

namespace IPP\Student\Runtime;

use IPP\Student\Exceptions\MessageException;
use RuntimeException;

class FrameStack {
    /** @var Frame[] */
    private array $stack = [];

    public function push(Frame $frame): void {
        array_push($this->stack, $frame);
    }

    public function pop(): Frame {
        return array_pop($this->stack);
    }

    public function top(): Frame {
        return end($this->stack);
    }

    public function set(string $name, mixed $value): void {
        $this->top()->set($name, $value);
    }

    public function get(string $name): mixed {
        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
            if ($this->stack[$i]->has($name)) {
                return $this->stack[$i]->get($name);
            }
        }
        throw new MessageException("Variable '$name' not found in any frame.");
    }
}