<?php

namespace IPP\Student\Classes;

class Node {

    public function print(int $indentLevel = 0): void {
        $indent = str_repeat('  ', $indentLevel);
        echo $indent;
    }
}
?>