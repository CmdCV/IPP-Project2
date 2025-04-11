<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Exceptions\MessageException;

class ClassReference extends ObjectInstance
{
    public function __construct(private SolClass $classRef)
    {
        parent::__construct($classRef); // nebo třídu 'Class' pokud máš
    }

    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        return match ($selector) {
            'new' => $this->classRef->instantiate(),
            'from:' => $this->classRef->instantiateFrom($args[0]),
            'read' => $this->handleRead(),
            default => throw new MessageException("Class does not understand $selector")
        };
    }

    private function handleRead(): ObjectInstance
    {
        if ($this->classRef->isSubclassOf('String')) {
            $line = rtrim(fgets(STDIN), "\\r\\n");
            return ObjectFactory::string($line);
        }

        throw new MessageException("read not supported on {$this->classRef->getName()}");
    }

}
