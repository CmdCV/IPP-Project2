<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;

class Send extends Node
{
    private string $selector;
    private Expr $expr; // receiver
    /** @var Arg[] */
    private array $arguments;

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

    public function __construct(string $selector, Expr $expr, array $arguments = [])
    {
        $this->selector = $selector;
        $this->expr = $expr;
        $this->arguments = $arguments;
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

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        // 1. Vyhodnotíme příjemce zprávy (např. self, nebo (String read))
        $target = $this->expr->execute($self, $frame);

        // 2. Vyhodnotíme všechny argumenty
        $args = [];
        foreach ($this->arguments as $argExpr) {
            $args[] = $argExpr->execute($self, $frame);
        }

        // 3. Zasíláme zprávu přes sendMessage()
        if (method_exists($target, 'sendMessage')) {
            return $target->sendMessage($this->selector, $args);
        }

        // 4. Chyba – zprávu nelze poslat (např. interní objekt nebo špatný typ)
        throw new MessageException("Cannot send message to target");
    }
}
