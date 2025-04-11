<?php

namespace IPP\Student\Classes;

use DOMElement;

class Program extends Node
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

    public static function fromXML(DOMElement $node): self
    {
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

    public function addClass(SolClass $class)
    {
        $this->classes[] = $class;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function findClassByName(string $name): SolClass
    {
        foreach ($this->classes as $class) {
            if ($class->getName() === $name) {
                return $class;
            }
        }
        throw new MessageException("Class '{$name}' not found when searching in Program.");
    }
}