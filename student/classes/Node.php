<?php

namespace IPP\Student\Classes;
use IPP\Student\RunTime\ObjectFrame;
use IPP\Student\RunTime\ObjectInstance;
use ReflectionClass;
use ReflectionObject;

abstract class Node implements Parsable
{
    public function __toString(): string
    {
        return $this->prettyPrint();
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        $className = (new ReflectionClass($this))->getShortName();
        $out = "$pad$className {\n";

        $ref = new ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {
            $key = $prop->getName();
            $value = $prop->getValue($this);

            $out .= $pad . '  ' . $key . ': ';
            if ($value instanceof Node) {
                $out .= "\n" . $value->prettyPrint($indent + 2);
            } elseif (is_array($value)) {
                $out .= "[\n";
                foreach ($value as $item) {
                    if ($item instanceof Node) {
                        $out .= $item->prettyPrint($indent + 3);
                    } else {
                        $out .= str_repeat('  ', $indent + 3) . print_r($item, true);
                    }
                }
                $out .= $pad . "  ]\n";
            } else {
                $out .= print_r($value, true) . "\n";
            }
        }

        $out .= "$pad\n";
        return $out;
    }

    public abstract function execute(ObjectInstance $self, ObjectFrame $frame): ObjectInstance;
}
