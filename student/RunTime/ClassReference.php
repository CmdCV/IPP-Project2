<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Exceptions\MessageException;

class ClassReference extends ObjectInstance
{
    public function __construct(SolClass $class)
    {
        parent::__construct($class); // nebo třídu 'Class' pokud máš
    }

    /**
     * @throws MessageException
     */
    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        return match ($selector) {
            'new' => $this->getClass()->instantiate(),
            'from:' => $this->getClass()->instantiateFrom($args[0]),
            'read' => $this->handleRead(),
            default => throw new MessageException("Class does not understand $selector")
        };
    }

    /**
     * @throws MessageException
     */
    private function handleRead(): ObjectInstance
    {
        if ($this->getClass()->isSubclassOf('String')) {
            $line = rtrim(fgets(STDIN), "\\r\\n");
            return ObjectFactory::string($line);
        }

        throw new MessageException("read not supported on {$this->getClass()->getName()}");
    }

}
