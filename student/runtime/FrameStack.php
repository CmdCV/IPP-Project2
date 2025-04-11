<?php

namespace IPP\Student\Runtime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Exceptions\MessageException;
use RuntimeException;

class FrameStack {
    /** @var Frame[] */
    private array $stack = [];
    private ObjectInstance $trueInstance;
    private ObjectInstance $falseInstance;
    private ObjectInstance $nilInstance;

    public function getTrue(): ObjectInstance
    {
        return $this->trueInstance;
    }

    public function getFalse(): ObjectInstance
    {
        return $this->falseInstance;
    }

    public function getNil(): ObjectInstance
    {
        return $this->nilInstance;
    }
    public function __construct()
    {
        $this->trueInstance = new ObjectInstance(new SolClass('True', 'Object'), []);
        $this->falseInstance = new ObjectInstance(new SolClass('False', 'Object'), []);
        $this->nilInstance = new ObjectInstance(new SolClass('Nil', 'Object'), []);
    }

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
        throw new MessageException("Variable '{$name}' not found in any frame.");
    }
}