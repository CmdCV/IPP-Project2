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
        $this->debugLog("→ Sending message '$selector' to instance of class ".$this->class->getName());
        // Normální metoda
        $method = $this->class->findMethod($selector);
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
     * @throws MessageException
     * @throws ValueException
     */
    private function handleRead(string $class): ObjectInstance
    {
        $line="";
        switch ($class) {
            case "String":
                $line = IOContext::$input->readString();
                break;
            case "Integer":
                $line = IOContext::$input->readInt();
                break;
            case "Boolean":
                $line = IOContext::$input->readBool();
                break;
        }
        if($line !== "") {
            return ObjectFactory::string(rtrim($line, "\n\r"));
        }

        throw new MessageException("read not supported on {$this->getClass()->getName()}");
    }

    /**
     * @throws TypeException
     * @throws ValueException
     * @throws MessageException
     */
    private function handleIntegerBuiltins(string $selector, array $args): ObjectInstance
    {
        $val = $this->attributes['__value'] ?? null;
        if (!is_int($val)&&!is_null($val)) throw new ValueException("Integer value missing, got: " . gettype($val));

        $this->debugLog("→ Integer triggered for '$selector' with this = (" . $val . "), args = [" . implode(', ', array_map(fn($a) => $a->getAttribute('__value') ?? 'undef', $args)) . "]");

        // TODO: timesRepeat:
        // Jako argument očekává instanci, která rozumí zprávě value:20. Pokud (a jen tehdy, když)
        // je příjemce n > 0, blok z argumentu se provede n-krát. Bloku resp. argumentu se předá
        // jako argument číslo iterace (od 1 do n včetně).
        return match ($selector) {
//            'read' => $this->handleRead("Integer"),
            'divBy:' => $this->requireIntArg($args, 0) === 0 ? throw new ValueException("Division by zero") : ObjectFactory::integer(intdiv($val, $this->requireIntArg($args, 0))),
            'equalTo:' => $val === $this->requireIntArg($args, 0) ? ObjectFactory::true() : ObjectFactory::false(),
            'greaterThan:' => $val > $this->requireIntArg($args, 0) ? ObjectFactory::true() : ObjectFactory::false(),
            'plus:' => ObjectFactory::integer($val + $this->requireIntArg($args, 0)),
            'minus:' => ObjectFactory::integer($val - $this->requireIntArg($args, 0)),
            'multiplyBy:' => ObjectFactory::integer($val * $this->requireIntArg($args, 0)),
//            'lessThan:' => $val < $this->requireIntArg($args, 0) ? ObjectFactory::true() : ObjectFactory::false(),
            'asString' => ObjectFactory::string((string)$val),
            'asInteger' => $this,
            'timesRepeat:' => (function () use ($val, $args) {
                if (!isset($args[0])) {
                    throw new ValueException("Missing block argument in timesRepeat:");
                }
                $block = $args[0];
//                if (!($block instanceof ObjectInstance) || !$block->getClass()->understandsMessage('value:')) {
//                    throw new TypeException("Expected block with value: method");
//                }
                if (!is_int($val)) {
                    throw new ValueException("Receiver is not an integer");
                }
                $this->debugLog("→ timesRepeat: executing block $val times");

                for ($i = 1; $i <= $val; $i++) {
                    $block->sendMessage('value:', [ObjectFactory::integer($i)]);
                }
                return ObjectFactory::nil();
            })(),
            'isString','isNil', 'isBlock' => ObjectFactory::false(),
            'isNumber' => ObjectFactory::true(),
            'whileTrue:' => $this->handleWhileTrue($args[0]),
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }

    /**
     * @throws TypeException
     * @throws ValueException
     * @throws MessageException
     */
    private function handleWhileTrue(ObjectInstance $block): ObjectInstance
    {
        while (true) {
            $condition = $this->sendMessage("value", []);
            if (!$condition->getClass()->isSubclassOf("Boolean")) {
                throw new MessageException("whileTrue: expects boolean result from value");
            }

            $boolVal = $condition->getAttribute('__value') ?? false;
            if (!$boolVal) {
                break;
            }

            $block->sendMessage("value", []);
        }

        return ObjectFactory::nil();
    }

    /**
     * @throws ValueException
     * @throws TypeException
     * @throws MessageException
     */
    private function handleStringBuiltins(string $selector, array $args): ObjectInstance
    {
        $val = $this->attributes['__value'] ?? null;
        if (!is_string($val)&&!is_null($val)) throw new ValueException("String value missing, got: " . $val);

        $this->debugLog("→ String triggered for '$selector' with this = (" . $val . "), args = [" . implode(', ', array_map(fn($a) => $a->getAttribute('__value') ?? 'undef', $args)) . "]");

        return match ($selector) {
            'read' => $this->handleRead("String"),
            'print' => (function () use ($val) {
                IOContext::$stdout->writeString($val);
                return $this;
            })(),
            'equalTo:' => $val === $this->requireStringArg($args, 0) ? ObjectFactory::true() : ObjectFactory::false(),
            'asString' => $this,
            'asInteger' => is_numeric($val) ? ObjectFactory::integer((int)$val) : ObjectFactory::nil(),
            'concatenateWith:' => (function () use ($val, $args) {
                $arg = $args[0] ?? null;
                if ($arg?->isInstanceOf(ObjectFactory::getClass('String'))) {
                    $argVal = $arg->getAttribute('__value') ?? '';
                    return ObjectFactory::string($val . $argVal);
                }
                return ObjectFactory::nil();
            })(),
            'startsWith:endsBefore:' => (function () use ($val, $args) {
                $start = $args[0]->getAttribute('__value') ?? null;
                $end = $args[1]->getAttribute('__value') ?? null;
                if (!is_int($start) || !is_int($end) || $start <= 0 || $end <= 0) {
                    return ObjectFactory::nil();
                }
                if ($end <= $start) {
                    return ObjectFactory::string('');
                }
                return ObjectFactory::string(substr($val, $start - 1, $end - $start));
            })(),
            'isNumber','isNil', 'isBlock' => ObjectFactory::false(),
            'isString' => ObjectFactory::true(),
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

        $this->debugLog("→ Boolean triggered for '$selector' with value = " . $this->class->getName() . " args = [" . (isset($args[0]) ? $args[0]->getClass()->getName() : 'undef') . "]");

        return match ($selector) {
//            'read' => $this->handleRead("Boolean"),
            'not' => $isTrue ? ObjectFactory::false() : ObjectFactory::true(),
            'and:' => $isTrue ? $args[0]->sendMessage('value', []) : ObjectFactory::false(),
            'or:' => $isTrue ? ObjectFactory::true() : $args[0]->sendMessage('value', []),
            'ifTrue:ifFalse:' =>(function () use ($args,$isTrue) {
                return $isTrue ? $args[0]->sendMessage('value', []) : $args[1]->sendMessage('value', []);
            })(),
            'identicalTo:' => $this->class->getName() === $args[0]->class->getName() ? ObjectFactory::true() : ObjectFactory::false(),
            'isNumber', 'isString','isNil', 'isBlock' => ObjectFactory::false(),
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
//            'print' => (function () {
//                IOContext::$stdout->writeString("nil");
//                return $this;
//            })(),
            'identicalTo:' => $this->class->getName() === $args[0]->class->getName() ? ObjectFactory::true() : ObjectFactory::false(),
            'isNumber', 'isString', 'isBlock' => ObjectFactory::false(),
            'isNil' => ObjectFactory::true(),
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    protected function handleObjectBuiltins(string $selector, array $args): ObjectInstance
    {
        $this->debugLog("→ Object triggered for '$selector' with this = (" . ($this->getAttribute('__value') ?? 'undef') . "), args = [" . implode(', ', array_map(fn($a) => $a->getAttribute('__value') ?? 'undef', $args)) . "]");
        return match ($selector) {
            'identicalTo:' => ($this === $args[0]) ? ObjectFactory::true() : ObjectFactory::false(),
            'equalTo:' => $this->shallowEqual($args[0]) ? ObjectFactory::true() : ObjectFactory::false(),
            'asString' => ObjectFactory::string(''),
            'isNumber', 'isString', 'isBlock', 'isNil' => ObjectFactory::false(),
            default => $this->handleFallbackAccessors($selector, $args),
        };
    }

    /**
     * @throws ValueException
     * @throws MessageException
     */
    private function handleFallbackAccessors(string $selector, array $args): ObjectInstance
    {
        $this->debugLog("→ Fallback access triggered for '$selector' with this = (" . ($this->getAttribute('__value') ?? 'undef') . "), args = [" . implode(', ', array_map(fn($a) => $a->getAttribute('__value') ?? 'undef', $args)) . "]");
        if (str_ends_with($selector, ':') && count($args) === 1) {
            $attr = rtrim($selector, ':');
            $this->debugLog("SET '$attr' := " . ($args[0]->getAttribute('__value') ?? 'undef'));
            $this->attributes[$attr] = $args[0];
            $this->debugLog("Attributes available: [" . implode(', ', array_keys($this->attributes)) . "]");
            return $args[0];
        }

        if (count($args) === 0) {
            $val = $this->attributes[$selector] ?? null;
            $this->debugLog("Attributes available: [" . implode(', ', array_keys($this->attributes)) . "]");
            if ($val === null) {
                $this->debugLog("GET '$selector' = nil");
                throw new MessageException("Attribute '$selector' not found");
            }
            $this->debugLog("GET '$selector' = " . ($val->getAttribute('__value') ?? 'undef'));
            return $val;
        }

        throw new MessageException("Unknown message $selector");
    }

    private function shallowEqual(ObjectInstance $other): bool
    {
        $this->debugLog("→ Shallow equal check for " . $this->getClass()->getName() . " and " . $other->getClass()->getName());
        return $this->getClass() === $other->getClass()
            && $this->getAllAttributes() === $other->getAllAttributes();
    }

    /**
     * @throws ValueException
     */
    private function requireIntArg(array $args, int $i): int
    {
        $val = $args[$i]?->getAttribute('__value') ?? null;
        if (!is_int($val)) {
            throw new ValueException("Expected Integer argument");
        }
        return $val;
    }

    /**
     * @throws ValueException
     */
    private function requireStringArg(array $args, int $i): string
    {
        $val = $args[$i]?->getAttribute('__value') ?? null;
        if (!is_string($val)) {
            throw new ValueException("Expected String argument");
        }
        return $val;
    }


    public function debugLog(string $message, int $indent = 0): void
    {
        if (IOContext::$debug) {
            $pad = str_repeat('  ', $indent);
            IOContext::$stderr->writeString("[DEBUG] $pad$message\n");
        }
    }
}
