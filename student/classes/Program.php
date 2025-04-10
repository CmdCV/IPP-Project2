<?php

namespace IPP\Student\Classes;

use DOMElement;

class Program implements Node
{
    private string $language;
    private string $description;
    /** @var SolClass[] */
    private array $classes;

    public function __construct(string $language, string $description, array $classes = [])
    {
        $this->language = $language;
        $this->description = $description;
        $this->classes = $classes;
    }

    public function addClass(SolClass $class)
    {
        $this->classes[] = $class;
    }

    public function print($indentLevel = 0): void
    {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent . "Program:\n";
        echo $indent . "  Language: {$this->language}\n";
        echo $indent . "  Description: {$this->description}\n";
        echo $indent . "  Classes:\n";
        foreach ($this->classes as $class) {
            $class->print($indentLevel + 2);
        }
    }

    public static function fromXML(DOMElement $node): self {
        $language = $node->getAttribute('language');
        $description = $node->getAttribute('description');
        $program = new self($language, $description);

        foreach ($node->getElementsByTagName('class') as $classNode) {
            if ($classNode instanceof DOMElement) {
                $program->addClass(SolClass::fromXML($classNode));
            }
        }

        return $program;
    }
}