<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Core\AbstractInterpreter;
use IPP\Student\Classes\XMLParser;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        $dom = $this->source->getDOMDocument();
        $parser = new XMLParser($dom);
        $program = $parser->parseProgram();
        echo $program;
        $program->start();
        return ReturnCode::OK;

        // Check IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");
    }
}
