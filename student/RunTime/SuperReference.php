<?php

namespace IPP\Student\RunTime;

use IPP\Student\Exceptions\MessageException;

class SuperReference extends ObjectInstance
{
    private ObjectInstance $self;
    public function __construct(ObjectInstance $instance) {
        $this->self = $instance;
        parent::__construct($instance->getClass());
    }

    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        $class = $this->self->getClass()->getParent();
        while ($class !== null) {
            $method = $class->findMethod($selector);
            if ($method !== null) {
                return $method->invoke($this->self, $args);
            }

            // pokud narazíme na vestavěnou třídu, zkusíme její vestavěný handler
            $builtinNames = ['Integer', 'String', 'True', 'False', 'Nil', 'Object'];
            if (in_array($class->getName(), $builtinNames)) {
                $tmp = new ObjectInstance($class);
                $tmp->setAttribute('__value', $this->self->getAttribute('__value') ?? null);
                return $tmp->sendMessage($selector, $args);
            }

            $class = $class->getParent();
        }

        throw new MessageException("super does not understand '$selector'");
    }
}
