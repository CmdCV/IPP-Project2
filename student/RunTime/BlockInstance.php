<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\Block;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;

class BlockInstance extends ObjectInstance
{
    private Block $block;
    private ObjectInstance $definingSelf;

    /**
     * @throws ValueException
     */
    public function __construct(Block $block, ObjectInstance $self)
    {
        parent::__construct(ObjectFactory::getClass("Block"));
        $this->block = $block;
        $this->definingSelf = $self;
    }

    /**
     * @param array<ObjectInstance> $args
     * @throws MessageException
     * @throws ValueException
     * @throws TypeException
     */
    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        $arity = substr_count($selector, ':');

        return match ($selector) {
            'isNumber', 'isNil', 'isString' => ObjectFactory::false(),
            'isBlock' => ObjectFactory::true(),
            str_repeat('value:', $arity) => (function () use ($arity, $args) {
                if ($arity !== $this->block->getArity()) {
                    throw new MessageException("Block arity mismatch");
                }

                $frame = new ObjectFrame();
                foreach ($this->block->getParameters() as $i => $param) {
                    $frame->set($param->getName(), $args[$i]);
                }

                return $this->block->execute($this->definingSelf, $frame);
            })(),
            'value' => $this->block->execute($this->definingSelf, new ObjectFrame()),
            'whileTrue:' => (function () use ($args) {
                $result = ObjectFactory::nil();
                while (true) {
                    $cond = $this->sendMessage('value', []);
                    if ($cond->getClass()->getName() !== 'True') {
                        break;
                    }
                    $result = $args[0]->sendMessage('value', []);
                }
                return $result;
            })(),
            default => $this->handleObjectBuiltins($selector, $args),
        };
    }
}
