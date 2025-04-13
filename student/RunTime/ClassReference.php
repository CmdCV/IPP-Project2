<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\SolClass;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;

class ClassReference extends ObjectInstance
{
    public function __construct(SolClass $class)
    {
        parent::__construct($class); // nebo třídu 'Class' pokud máš
    }

    /**
     * @throws ValueException
     * @throws MessageException
     * @throws TypeException
     */
    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        return match ($selector) {
            'new' => $this->getClass()->instantiate(),
            'from:', 'super:' => $this->getClass()->instantiateFrom($args[0]),
            default => parent::sendMessage($selector, $args),
        };
    }

}
