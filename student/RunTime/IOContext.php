<?php

namespace IPP\Student\RunTime;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;

class IOContext
{
    public static InputReader $input;
    public static OutputWriter $stdout;
    public static OutputWriter $stderr;
    public static bool $debug = false;
}
