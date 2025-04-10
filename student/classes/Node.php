<?php

namespace IPP\Student\Classes;

interface Node extends Parsable {
    public function print(int $indentLevel = 0): void;
}
?>