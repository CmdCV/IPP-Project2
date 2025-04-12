<?php

namespace IPP\Student\RunTime;

use IPP\Student\Classes\Block;
use IPP\Student\Exceptions\MessageException;
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
     * @throws MessageException
     */
    public function sendMessage(string $selector, array $args): ObjectInstance
    {
        $arity = substr_count($selector, ':');

        // value, value:, value:value:
        if ($selector === str_repeat('value:', $arity)) {
            if ($arity !== $this->block->getArity()) {
                throw new MessageException("Block arity mismatch");
            }

            $frame = new ObjectFrame();
            foreach ($this->block->getParameters() as $i => $param) {
                $frame->set($param->getName(), $args[$i]);
            }

            return $this->block->execute($this->definingSelf, $frame);
        }

        // ⛔️ Chybí: čistý `value` bez dvojtečky
        if ($selector === 'value' && $this->block->getArity() === 0) {
            return $this->block->execute($this->definingSelf, new ObjectFrame());
        }

        // whileTrue:
        if ($selector === 'whileTrue:') {
            $result = ObjectFactory::nil();
            while (true) {
                $cond = $this->sendMessage('value', []);
                if ($cond->getClass()->getName() !== 'True') {
                    break;
                }
                $result = $args[0]->sendMessage('value', []);
            }
            return $result;
        }

        throw new MessageException("Block does not understand $selector");
    }
}
