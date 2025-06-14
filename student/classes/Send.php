<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;

class Send extends Node
{
    private string $selector;
    private Expr $expr; // receiver
    /** @var array<Arg> */
    private array $arguments;

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getExpr(): Expr
    {
        return $this->expr;
    }

    /**
     * @return array<Arg>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array<Arg> $arguments
     */
    public function __construct(string $selector, Expr $expr, array $arguments = [])
    {
        $this->selector = $selector;
        $this->expr = $expr;
        usort($arguments, fn(Arg $a, Arg $b) => $a->getOrder() <=> $b->getOrder());
        $this->arguments = $arguments;
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        $out = $pad . "Send {\n";
        $out .= $pad . "  selector: $this->selector\n";
        $out .= $pad . "  receiver:\n" . $this->expr->prettyPrint($indent + 2);
        if (!empty($this->arguments)) {
            $out .= $pad . "  arguments:\n";
            foreach ($this->arguments as $arg) {
                $out .= $arg->prettyPrint($indent + 2);
            }
        }
        $out .= $pad . "}\n";
        return $out;
    }

    /**
     * @throws FileStructureException
     */
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

    /**
     * @throws ValueException
     * @throws MessageException
     * @throws TypeException
     */
    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        $target = $this->expr->execute($self, $frame);

        $args = [];
        foreach ($this->arguments as $argExpr) {
            $args[] = $argExpr->execute($self, $frame);
        }

        if (method_exists($target, 'sendMessage')) {
            return $target->sendMessage($this->selector, $args);
        }

        throw new MessageException("Cannot send message to target");
    }
}
