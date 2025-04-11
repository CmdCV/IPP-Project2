<?php

namespace IPP\Student\RunTime;

use IPP\Student\Exceptions\MessageException;

class SuperReference
{
    public function __construct(private ObjectInstance $self) {}

    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        $parent = $this->self->class->getParent();
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
