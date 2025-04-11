<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Runtime\ObjectFactory;
use IPP\Student\Runtime\BlockInstance;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;

class ObjectInstance
{
    public function __construct(
        public SolClass $class,
        public array $attributes = []
    ) {}

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function getAllAttributes(): array
    {
        return $this->attributes;
    }

    public function isInstanceOf(SolClass $class): bool
    {
        $current = $this->class;
        while ($current !== null) {
            if ($current === $class) return true;
            $parentName = $current->getParent();
            if (is_string($parentName)) {
                $current = ObjectFactory::getClass($parentName);
            } else {
                $current = $parentName;
            }
        }
        return false;
    }

    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        $this->debug_log("→ Sending message '{$selector}' to instance of class '{$this->class->getName()}'");
        // Normální metoda
        $method = $this->class->findMethod($selector, count($args));
        if ($method) {
            return $method->invoke($this, $args);
        }
        // Builtin fallback
        return match ($this->class->getName()) {
            'Integer' => $this->handleIntegerBuiltins($selector, $args),
            'String' => $this->handleStringBuiltins($selector, $args),
            'Object' => $this->handleObjectBuiltins($selector, $args),
            'True', 'False' => $this->handleBooleanBuiltins($selector, $args),
            'Nil' => $this->handleNilBuiltins($selector, $args),
            default => $this->handleFallbackAccessors($selector, $args),
        };
    }

    private function handleIntegerBuiltins(string $selector, array $args): ObjectInstance
    {
        $val = $this->attributes['__value'] ?? null;
        if (!is_int($val)) throw new ValueException("Integer value missing");

        return match ($selector) {
            'plus:' => ObjectFactory::integer($val + $this->requireIntArg($args, 0)),
            'minus:' => ObjectFactory::integer($val - $this->requireIntArg($args, 0)),
            'multiplyBy:' => ObjectFactory::integer($val * $this->requireIntArg($args, 0)),
            'divBy:' => $this->requireIntArg($args, 0) === 0
                ? throw new MessageException("Division by zero")
                : ObjectFactory::integer(intdiv($val, $this->requireIntArg($args, 0))),
            'greaterThan:' => $val > $this->requireIntArg($args, 0)
                ? ObjectFactory::true() : ObjectFactory::false(),
            'asString' => ObjectFactory::string((string)$val),
            'asInteger' => $this,
            default => throw new MessageException("Unknown Integer method $selector"),
        };
    }

    private function handleStringBuiltins(string $selector, array $args): ObjectInstance
    {
        $val = $this->attributes['__value'] ?? null;
        if (!is_string($val)) throw new ValueException("String value missing");

        return match ($selector) {
            'print' => (function () use ($val) {
                fwrite(STDOUT, $val);
                return $this;
            })(),
            'asString' => $this,
            'asInteger' => is_numeric($val) ? ObjectFactory::integer((int)$val) : ObjectFactory::nil(),
            'concatenateWith:' => ObjectFactory::string($val . ($args[0]->getAttribute('__value') ?? '')),
            'startsWith:endsBefore:' => (function () use ($val, $args) {
                $start = $args[0]->getAttribute('__value') ?? null;
                $end = $args[1]->getAttribute('__value') ?? null;
                if (!is_int($start) || !is_int($end) || $start < 1 || $end <= $start) {
                    return ObjectFactory::string('');
                }
                return ObjectFactory::string(substr($val, $start - 1, $end - $start));
            })(),
            default => throw new MessageException("Unknown String method $selector"),
        };
    }

    private function handleBooleanBuiltins(string $selector, array $args): ObjectInstance
    {
        $isTrue = $this->class->getName() === 'True';

        return match ($selector) {
            'not' => $isTrue ? ObjectFactory::false() : ObjectFactory::true(),
            'and:' => $isTrue ? $args[0]->sendMessage('value', []) : ObjectFactory::false(),
            'or:' => $isTrue ? ObjectFactory::true() : $args[0]->sendMessage('value', []),
            'ifTrue:ifFalse:' => $isTrue
                ? $args[0]->sendMessage('value', [])
                : $args[1]->sendMessage('value', []),
            default => throw new MessageException("Unknown Boolean method $selector"),
        };
    }

    private function handleObjectBuiltins(string $selector, array $args): ObjectInstance
    {
        return match ($selector) {
            'identicalTo:' => ($this === $args[0]) ? ObjectFactory::true() : ObjectFactory::false(),
            'equalTo:' => $this->shallowEqual($args[0]) ? ObjectFactory::true() : ObjectFactory::false(),
            'asString' => ObjectFactory::string(''),
            'isNumber' => ObjectFactory::false(),
            'isString' => ObjectFactory::false(),
            'isBlock' => ObjectFactory::false(),
            'isNil' => ObjectFactory::false(),
            default => throw new MessageException("Unknown Object method $selector"),
        };
    }

    private function shallowEqual(ObjectInstance $other): bool
    {
        return $this->class === $other->class
            && $this->attributes === $other->attributes;
    }

    private function handleNilBuiltins(string $selector, array $args): ObjectInstance
    {
        return match ($selector) {
            'asString' => ObjectFactory::string('nil'),
            default => throw new MessageException("Unknown Nil method $selector"),
        };
    }

    private function requireIntArg(array $args, int $i): int
    {
        $val = $args[$i]?->getAttribute('__value') ?? null;
        if (!is_int($val)) {
            throw new TypeException("Expected Integer argument");
        }
        return $val;
    }

    private function handleFallbackAccessors(string $selector, array $args): ObjectInstance
    {
        // Setter fallback
        if (str_ends_with($selector, ':') && count($args) === 1) {
            $attr = rtrim($selector, ':');
            $this->debug_log("SET '$attr' := " . ($args[0]->getAttribute('__value') ?? 'undef'));
            $this->attributes[$attr] = $args[0];
            return $this;
        }

        // Getter fallback
        if (count($args) === 0) {
            $val = $this->attributes[$selector] ?? null;
            $this->debug_log("GET '$selector' = " . ($val?->getAttribute('__value') ?? 'undef'));
            return $val ?? ObjectFactory::nil();
        }

        throw new MessageException("Unknown message $selector");
    }

    public function debug_log(string $message, int $indent = 0): void
    {
        $pad = str_repeat('  ', $indent);
        fwrite(STDERR, "[DEBUG] {$pad}{$message}\n");
    }
}
