<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;
use IPP\Core\AbstractInterpreter;
use IPP\Student\Classes\XMLParser;
use IPP\Student\Exceptions\FileStructureException;
use IPP\Student\Exceptions\MessageException;
use IPP\Student\Exceptions\TypeException;
use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\IOContext;

class Interpreter extends AbstractInterpreter
{
    /**
     * @throws ValueException
     * @throws FileStructureException
     * @throws MessageException
     * @throws TypeException
     */
    public function execute(): int
    {
        IOContext::$input = $this->input;
        IOContext::$stdout = $this->stdout;
        IOContext::$stderr = $this->stderr;
        IOContext::$debug = false;

        $dom = $this->source->getDOMDocument();
        $parser = new XMLParser($dom);
        $program = $parser->parseProgram();
        fwrite(STDERR, $program);
        $program->start();
        return ReturnCode::OK;

        // Check IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");
    }
}
