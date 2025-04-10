<?php

namespace IPP\Student\Runtime;

class Value {
    public function __construct(
        public readonly string $type,
        public readonly mixed $value
    ) {}

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
