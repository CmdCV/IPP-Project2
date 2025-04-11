<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Runtime\Frame;
use IPP\Student\Runtime\FrameStack;
use IPP\Student\Runtime\ObjectInstance;
use IPP\Student\Runtime\Value;

class Send extends Node
{
    private string $selector;
    private Expr $expr;
    /** @var Arg[] */
    private array $arguments;

    public function __construct(string $selector, Expr $expr, array $arguments = [])
    {
        $this->selector = $selector;
        $this->expr = $expr;
        $this->arguments = $arguments;
    }

    public static function fromXML(DOMElement $node): self
    {
        $selector = $node->getAttribute('selector');

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

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getExpr(): Expr
    {
        return $this->expr;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        $out = "{$pad}Send {\n";
        $out .= "{$pad}  selector: {$this->selector}\n";
        $out .= "{$pad}  receiver:\n" . $this->expr->prettyPrint($indent + 2);
        if (!empty($this->arguments)) {
            $out .= "{$pad}  arguments:\n";
            foreach ($this->arguments as $arg) {
                $out .= $arg->prettyPrint($indent + 2);
            }
        }
        $out .= "{$pad}}\n";
        return $out;
    }
}
