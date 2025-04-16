<?php

namespace IPP\Student\Classes;

use DOMElement;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectFactory;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use LogicException;

class Program extends Node
{
    private string $language;
    private string $description;
    /** @var SolClass[] */
    private array $classes;

    public function addClass(SolClass $class): void
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

    public function __construct(string $language, string $description, array $classes = [])
    {
        $this->language = $language;
        $this->description = $description;
        $this->classes = $classes;
    }

    /**
     * @throws FileStructureException
     */
    public static function fromXML(DOMElement $node): self
    {
        $language = $node->getAttribute('language');
        $description = $node->getAttribute('description');
        $program = new self($language, $description);

        foreach ($node->getElementsByTagName('class') as $classNode) {
            $program->addClass(SolClass::fromXML($classNode));
        }

        return $program;
    }


    /**
     * @throws TypeException
     */
    public function findClassByName(string $name): SolClass
    {
        foreach ($this->classes as $class) {
            if ($class->getName() === $name) {
                return $class;
            }
        }
        throw new TypeException("Class '$name' not found when searching in Program.");
    }

    public function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance
    {
        throw new LogicException("Program node cannot be directly executed, use start() instead.");
    }

    /**
     * @throws MessageException
     * @throws ValueException
     * @throws TypeException
     */
    public function start(): void
    {
        $this->registerBuiltins();

        $this->linkInheritance();

        foreach ($this->classes as $class) {
            ObjectFactory::registerClass($class);
        }

        $mainClass = $this->findClassByName("Main");
        if($mainClass->findMethod("run") === null) {
            throw new TypeException("Main class does not have a run method.");
        }
        $mainClass->instantiate()->sendMessage("run", []);
    }

    private function registerBuiltins(): void
    {
        $this->addClass(new SolClass('Object', ''));
        $names = ['Integer', 'String', 'True', 'False', 'Nil', 'Block'];
        foreach ($names as $name) {
            $this->addClass(new SolClass($name, 'Object'));
        }
    }

    /**
     * @throws ValueException
     */
    public function linkInheritance(): void
    {
        $classMap = [];
        foreach ($this->classes as $class) {
            $classMap[$class->getName()] = $class;
        }

        foreach ($this->classes as $class) {
            $class->linkParent($classMap);
        }
    }
}
