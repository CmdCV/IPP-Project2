<?php

namespace IPP\Student\Classes;

use DOMElement;

use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Runtime\Value;
use IPP\Student\Runtime\Frame;
use IPP\Student\Runtime\FrameStack;
use IPP\Student\Runtime\ObjectInstance;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\Exceptions\MessageException;

class Send implements Node {
    private string $selector;
    private Expr $expr;
    /** @var Arg[] */
    private array $arguments;

    public function getSelector(): string { return $this->selector; }
    public function getExpr(): Expr { return $this->expr; }
    public function getArguments(): array { return $this->arguments; }

    public function __construct(string $selector, Expr $expr, array $arguments = []) {
        $this->selector = $selector;
        $this->expr = $expr;
        $this->arguments = $arguments;
    }

    public function evaluate(FrameStack $stack): Value {
        $receiverValue = $this->expr->evaluate($stack);

        $argValues = [];
        foreach ($this->arguments as $arg) {
            $argValues[] = $arg->getExpr()->evaluate($stack);
        }

        // třídní zpráva (např. Integer new)
        if ($receiverValue->getType() === 'class') {
            return $this->handleClassMessage($receiverValue->getValue(), $this->selector, $argValues);
        }

        if ($receiverValue->getType() !== 'Object') {
            throw new TypeException("Cannot send message '{$this->selector}' to non-object. Got type '{$receiverValue->getType()}' with value: " . var_export($receiverValue->getValue(), true));
        }

        /** @var ObjectInstance $object */
        $object = $receiverValue->getValue();

        echo "[Send] evaluate -> " . $receiverValue->getType()." ".$receiverValue->getValue()->getClass()->getName()." ".$this->selector."\n";

        // === OPRAVA: přímé zpracování Integer greaterThan:
        if ($object->getClass()->getName() === 'Integer') {
            if ($this->selector === 'greaterThan:') {
                $left = $object->getAttribute('value')->getValue();
                $rightArg = $argValues[0];
                if ($rightArg->getType() === 'Object') {
                    $rightArg = $rightArg->getValue()->getAttribute('value');
                }
                $right = $rightArg->getValue();

                if (!is_numeric($left) || !is_numeric($right)) {
                    throw new TypeException("Comparison: expects numeric operands, got: a=" . var_export($left, true) . ", b=" . var_export($right, true));
                }
                $res = match ($this->selector) {
                    'greaterThan:' => $left > $right,
                    'lessThan:' => $left < $right,
                    'equalTo:' => $left === $right,
                };

                $obj = $res ? $stack->getTrue() : $stack->getFalse();
                return new Value('Object', $obj);
            }
        }

        $builtinResult = $this->handleBuiltin($object, $this->selector, $argValues, $stack);
        if ($builtinResult !== null) {
            return $builtinResult;
        }

        $method = $object->findMethod($this->selector);

        $frame = new Frame();
        $frame->set("self", $receiverValue);

        $parameters = $method->getBlock()->getParameters();
        foreach ($parameters as $i => $param) {
            $frame->set($param->getName(), $argValues[$i] ?? null);
        }

        $stack->push($frame);
        $result = $method->getBlock()->execute($stack);
        $stack->pop();

        return $result ?? new Value('Nil', null);
    }

    private function handleClassMessage(string $className, string $selector, array $args): Value
    {
        if ($selector === 'new') {
            return $this->createBuiltinInstance($className);
        }

        if ($selector === 'from:') {
            return $this->cloneFrom($className, $args[0]);
        }

        throw new MessageException("Class '{$className}' does not support selector '{$selector}'");
    }

    private function createBuiltinInstance(string $className): Value
    {
        $attributes = [];
        if ($className === 'Integer') {
            $attributes['value'] = new Value('Integer', 0);
        } elseif ($className === 'String') {
            $attributes['value'] = new Value('String', '');
        } elseif ($className === 'True') {
            return new Value('True', true);
        } elseif ($className === 'False') {
            return new Value('False', false);
        } elseif ($className === 'Nil') {
            return new Value('Nil', null);
        }

        return new Value('Object', new ObjectInstance(new SolClass($className, 'Object'), $attributes));
    }

    private function cloneFrom(string $className, Value $source): Value
    {
        if ($source->getType() !== 'Object') {
            throw new TypeException("from: expects an object. Got '{$source->getType()}'");
        }

        $sourceObj = $source->getValue();
        if (!($sourceObj instanceof ObjectInstance)) {
            throw new TypeException("Invalid source object in from:. Got: " . get_debug_type($sourceObj));
        }

        return new Value('Object', new ObjectInstance(
            new SolClass($className, 'Object'),
            $sourceObj->getAttributes())
        );
    }

    private function handleBuiltin(ObjectInstance $object, string $selector, array $args, FrameStack $stack): ?Value
    {

        // GETTER: bez argumentů, selector = název atributu
        if (count($args) === 0 && $object->hasAttribute($selector)) {
            return $object->getAttribute($selector);
        }

        // SETTER: jeden argument, selector končí dvojtečkou
        if (count($args) === 1 && str_ends_with($selector, ':')) {
            $attrName = rtrim($selector, ':');
            $object->setAttribute($attrName, $args[0]);
            return new Value('Object', $object); // setter vrací self
        }

        $className = $object->getClass()->getName();

        echo "[Send] handleBuiltin -> ".$className.' '.$selector."\n";

        if ($selector === 'asString') {
            return match ($className) {
                'String' => new Value('String', $object),
                'Integer' => new Value('String', (string) $object->getAttribute('value')->getValue()),
                'Nil' => new Value('String', 'nil'),
                'True' => new Value('String', 'true'),
                'False' => new Value('String', 'false'),
                default => new Value('String', '')
            };
        }

        if ($selector === 'asInteger') {
            if ($className === 'String') {
                $raw = $object->getAttribute('value')->getValue();
                return is_numeric($raw) ? new Value('Integer', (int)$raw) : new Value('Nil', null);
            }
            return new Value('Nil', null);
        }

        if ($className === 'String') {
            if ($selector === 'print') {
                echo $object->getAttribute('value')->getValue();
                return new Value('String', $object);
            }
            if ($selector === 'startsWith:endsBefore:') {
                $str = $object->getAttribute('value')->getValue();
                $start = $args[0]->getValue();
                $end = $args[1]->getValue();

                if (!is_int($start) || !is_int($end) || $start < 0 || $end < $start || $end > strlen($str)) {
                    throw new ValueException("Invalid range for startsWith:endsBefore:. Got start=$start, end=$end, string='" . $str . "'");
                }

                return new Value('String', substr($str, $start, $end - $start));
            }
        }


        if ($className === 'Integer') {
            $a = $object->getAttribute('value')->getValue();

            return match ($selector) {
                'plus:' => (function () use ($a, $args) {
                    $bVal = $args[0];
                    if ($bVal->getType() === 'Object') {
                        $bVal = $bVal->getValue()->getAttribute('value');
                    }
                    $b = $bVal->getValue();

                    if (!is_numeric($a) || !is_numeric($b)) {
                        throw new TypeException("plus: expects Integer operands, got: a=" . var_export($a, true) . ", b=" . var_export($b, true));
                    }

                    return new Value('Integer', (int)$a + (int)$b);
                })(),
                'negated' => new Value('Integer', -$a),
                'timesRepeat:' => (function () use ($a, $args, $stack) {
                    if ($a < 0) {
                        throw new ValueException("timesRepeat: cannot repeat negative times ($a)");
                    }
                    for ($i = 0; $i < $a; $i++) {
                        $this->evaluateBlockArgument($args[0], $stack);
                    }
                    return new Value('Nil', null);
                })(),
                default => null
            };
        }

        if ($className === 'Block') {
            if (str_starts_with($selector, 'value')) {
                $arity = $object->getAttribute('arity')->getValue();
                if ($arity !== count($args)) {
                    throw new MessageException("Incorrect number of arguments for block '{$selector}' (expected $arity, got " . count($args) . ")");
                }

                $block = $object->getAttribute('block')->getValue();
                $frame = new Frame();
                $frame->set("self", $stack->get("self"));

                foreach ($block->getParameters() as $i => $param) {
                    $frame->set($param->getName(), $args[$i] ?? null);
                }

                $stack->push($frame);
                $result = $block->execute($stack);
                $stack->pop();

                return $result ?? new Value("Nil", null);
            }

            if (in_array($selector, ['whileTrue:', 'whileFalse:'])) {
                $conditionBlock = $object;
                $bodyBlock = $args[0]->getValue(); // druhý blok

                while (true) {
                    $condValue = $this->evaluateBlockArgument(new Value('Object', $conditionBlock), $stack);
                    $isTrue = $condValue->getType() === 'True';

                    if (($selector === 'whileTrue:' && !$isTrue) || ($selector === 'whileFalse:' && $isTrue)) {
                        break;
                    }

                    $this->evaluateBlockArgument(new Value('Object', $bodyBlock), $stack);
                }

                return new Value('Nil', null);
            }
        }

        if (in_array($className, ['True', 'False'])) {
            $isTrue = $className === 'True';

            if ($selector === 'ifTrue:ifFalse:') {
                $chosenBlock = $isTrue ? $args[0] : $args[1];
                return $this->evaluateBlockArgument($chosenBlock, $stack);
            }

            if ($selector === 'and:') {
                return $isTrue ? $this->evaluateBlockArgument($args[0], $stack) : new Value('False', false);
            }

            if ($selector === 'or:') {
                return $isTrue ? new Value('True', true) : $this->evaluateBlockArgument($args[0], $stack);
            }

            if ($selector === 'not') {
                return new Value($isTrue ? 'False' : 'True', !$isTrue);
            }
        }

        return null;
    }

    private function evaluateBlockArgument(Value $blockValue, FrameStack $stack): Value
    {
        if ($blockValue->getType() !== 'Object') {
            throw new TypeException("Expected block object, got '{$blockValue->getType()}'");
        }

        /** @var ObjectInstance $blockObj */
        $blockObj = $blockValue->getValue();

        if ($blockObj->getClass()->getName() !== 'Block') {
            throw new TypeException("Expected block class for ifTrue:/and:/... argument, got '" . $blockObj->getClass()->getName() . "'");
        }

        $arity = $blockObj->getAttribute('arity')->getValue();
        if ($arity !== 0) {
            throw new MessageException("Expected block with arity 0, got {$arity}");
        }

        $block = $blockObj->getAttribute('block')->getValue();
        $frame = new Frame();
        $frame->set("self", $stack->get("self"));

        $stack->push($frame);
        $result = $block->execute($stack);
        $stack->pop();

        return $result ?? new Value("Nil", null);
    }

    public function print(int $indentLevel = 0): void
    {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Send (selector: {$this->selector}):\n";
        echo $indent . "  Receiver: (Expression)\n";
        $this->expr->print($indentLevel + 2);
        if (!empty($this->arguments)) {
            echo $indent . "  Arguments:\n";
            foreach ($this->arguments as $arg) {
                $arg->print($indentLevel + 2);
            }
        }
    }

    public static function fromXML(DOMElement $node): self {
        $selector = $node->getAttribute('selector');
        echo "[XML] selector={$selector}\n";

        $receiver = null;
        $args = [];

        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) continue;

            switch ($child->nodeName) {
                case 'expr':
                    if ($receiver === null) {
                        $receiver = Expr::fromXML($child);
                    } else {
                        throw new FileStructureException("Multiple <expr> nodes in <send>, but only one receiver is allowed.");
                    }
                    break;
                case 'arg':
                    $args[] = Arg::fromXML($child);
                    break;
            }
        }

        if ($receiver === null) {
            throw new FileStructureException("<send> missing <expr> receiver");
        }

        return new self($selector, $receiver, $args);
    }
}
