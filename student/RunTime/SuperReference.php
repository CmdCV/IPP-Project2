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
        $parent = $this->self->getClass()->getParent();
        if (!$parent) {
            throw new MessageException("super used but no parent class exists");
        }

        $method = $parent->findMethod($selector, count($args));
        if (!$method) {
            throw new MessageException("super does not understand '$selector'");
        }

        return $method->invoke($this->self, $args); // invoke na původním self
    }
}
