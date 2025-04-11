<?php

namespace IPP\Student\Runtime;

class Value {
    private string $type;
    private mixed $value;
    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;

        echo "[Value] Constructing Value of type {$this->type}\n";
    }


    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function __toString(): string {
        return (string)$this->value;
    }

    public static function integer(int $value): self {
        return new self('Integer', $value);
    }

    public static function boolean(bool $value): self {
        return new self('Boolean', $value);
    }

    public static function string(string $value): self {
        return new self('String', $value);
    }
}
