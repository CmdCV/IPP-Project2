<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;

class ObjectInstance
{
    private SolClass $class;
    private array $attributes = [];

    public function __construct(SolClass $class)
    {
        $this->class = $class;
    }

    public function getClass(): SolClass
    {
        return $this->class;
    }

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
            $current = $current->getParent();
        }
        return false;
    }

    public function isAncestorOf(SolClass $class): bool
    {
        $current = $class;
        while ($current !== null) {
            if ($current === $this->class) return true;
            $current = $current->getParent();
        }
        return false;
    }

    /**
     * @throws ValueException
     * @throws MessageException
     * @throws TypeException
     */
    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        $this->debugLog("→ Sending message '$selector' to instance of class".$this->class->getName());
        // Normální metoda
        $method = $this->class->findMethod($selector, count($args));
        if ($method) {
            return $method->invoke($this, $args);
        }

        // Builtin fallback podle dědičnosti
        if ($this->isInstanceOf(ObjectFactory::getClass('Integer'))) {
            return $this->handleIntegerBuiltins($selector, $args);
        }
        if ($this->isInstanceOf(ObjectFactory::getClass('String'))) {
            return $this->handleStringBuiltins($selector, $args);
        }
        if ($this->isInstanceOf(ObjectFactory::getClass('True')) || $this->isInstanceOf(ObjectFactory::getClass('False'))) {
            return $this->handleBooleanBuiltins($selector, $args);
        }
        if ($this->isInstanceOf(ObjectFactory::getClass('Nil'))) {
            return $this->handleNilBuiltins($selector, $args);
        }
        if ($this->isInstanceOf(ObjectFactory::getClass('Object'))) {
            return $this->handleObjectBuiltins($selector, $args);
        }
        throw new MessageException("Unknown message $selector");
    }


    /**
     * @throws TypeException
     * @throws ValueException
     * @throws MessageException
     */
    private function handleIntegerBuiltins(string $selector, array $args): ObjectInstance
    {
        $val = $this->attributes['__value'] ?? null;
        if (!is_int($val)) throw new ValueException("Integer value missing");

        $this->debugLog("→ Integer triggered for '$selector' with value = $val");

        return match ($selector) {
            'plus:' => ObjectFactory::integer($val + $this->requireIntArg($args, 0)),
            'minus:' => ObjectFactory::integer($val - $this->requireIntArg($args, 0)),
            'multiplyBy:' => ObjectFactory::integer($val * $this->requireIntArg($args, 0)),
            'divBy:' => $this->requireIntArg($args, 0) === 0
                ? throw new MessageException("Division by zero")
                : ObjectFactory::integer(intdiv($val, $this->requireIntArg($args, 0))),
            'greaterThan:' => $val > $this->requireIntArg($args, 0)
                ? ObjectFactory::true() : ObjectFactory::false(),
            'lessThan:' => $val < $this->requireIntArg($args, 0)
                ? ObjectFactory::true() : ObjectFactory::false(),
            'equalTo:' => $val === $this->requireIntArg($args, 0)
                ? ObjectFactory::true() : ObjectFactory::false(),
            'asString' => ObjectFactory::string((string)$val),
            'asInteger' => $this,
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    private function handleStringBuiltins(string $selector, array $args): ObjectInstance
    {
        $val = $this->attributes['__value'] ?? null;
        if (!is_string($val)) throw new ValueException("String value missing");

        $this->debugLog("→ String triggered for '$selector' with value = $val");

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
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    private function handleBooleanBuiltins(string $selector, array $args): ObjectInstance
    {
        $isTrue = $this->class->getName() === 'True';

        $this->debugLog("→ Boolean triggered for '$selector' with value =" . $this->class->getName());

        return match ($selector) {
            'not' => $isTrue ? ObjectFactory::false() : ObjectFactory::true(),
            'and:' => $isTrue ? $args[0]->sendMessage('value', []) : ObjectFactory::false(),
            'or:' => $isTrue ? ObjectFactory::true() : $args[0]->sendMessage('value', []),
            'ifTrue:ifFalse:' => $isTrue
                ? $args[0]->sendMessage('value', [])
                : $args[1]->sendMessage('value', []),
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    private function handleNilBuiltins(string $selector, array $args): ObjectInstance
    {
        $this->debugLog("→ Nil triggered for '$selector'");
        return match ($selector) {
            'asString' => ObjectFactory::string('nil'),
            'print' => (function () {
                fwrite(STDOUT, "nil");
                return $this;
            })(),
            'isNumber', 'isString', 'isBlock' => ObjectFactory::false(),
            'isNil' => ObjectFactory::true(),
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    private function handleObjectBuiltins(string $selector, array $args): ObjectInstance
    {
        $this->debugLog("→ Object triggered for '$selector'");
        return match ($selector) {
            'identicalTo:' => ($this === $args[0]) ? ObjectFactory::true() : ObjectFactory::false(),
            'equalTo:' => $this->shallowEqual($args[0]) ? ObjectFactory::true() : ObjectFactory::false(),
            'isNumber', 'isString', 'isBlock', 'isNil' => ObjectFactory::false(),
            'asString' => ObjectFactory::string(''),
            default => $this->handleFallbackAccessors($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    private function handleFallbackAccessors(string $selector, array $args): ObjectInstance
    {
        $this->debugLog("→ Fallback access triggered for '$selector'");
        if (str_ends_with($selector, ':') && count($args) === 1) {
            $attr = rtrim($selector, ':');
            $this->debugLog("SET '$attr' := " . ($args[0]->getAttribute('__value') ?? 'undef'));
            $this->attributes[$attr] = $args[0];
            return $args[0];
        }

        if (count($args) === 0) {
            $val = $this->attributes[$selector] ?? null;
            if ($val === null) {
                $this->debugLog("GET '$selector' = nil");
                return ObjectFactory::nil();
            }
            $this->debugLog("GET '$selector' = " . ($val->getAttribute('__value') ?? 'undef'));
            return $val;
        }

        throw new MessageException("Unknown message $selector");
    }

    private function shallowEqual(ObjectInstance $other): bool
    {
        return $this->class === $other->class
            && $this->attributes === $other->attributes;
    }

    /**
     * @throws TypeException
     */
    private function requireIntArg(array $args, int $i): int
    {
        $val = $args[$i]?->getAttribute('__value') ?? null;
        if (!is_int($val)) {
            throw new TypeException("Expected Integer argument");
        }
        return $val;
    }


    public function debugLog(string $message, int $indent = 0): void
    {
        $pad = str_repeat('  ', $indent);
        fwrite(STDERR, "[DEBUG] $pad$message\n");
    }
}
