<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Core\AbstractInterpreter;
use IPP\Student\Classes\XMLParser;
use IPP\Student\Runtime\Value;
use IPP\Student\Runtime\Frame;
use IPP\Student\Runtime\FrameStack;
use IPP\Student\Runtime\ObjectInstance;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();

        $parser = new XMLParser($dom);
        $program = $parser->parseProgram();
//        $program->print();
        $mainClass = $program->findClassByName("Main");
        $runMethod = $mainClass->findMethodBySelector("run");

        $stack = new FrameStack();
        $frame = new Frame();

        $mainInstance = new ObjectInstance($mainClass);
        $frame->set("self", new Value("Object", $mainInstance));

        $stack->push($frame);

        $runMethod->getBlock()->execute($stack);

        // Check IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");
        return ReturnCode::OK;
    }
}
