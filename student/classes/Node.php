<?php

namespace IPP\Student\Classes;

abstract class Node implements Parsable
{

//    public abstract function execute(Node $node);

    public function debug_log(string $message, int $indent = 0): void
    {
        $pad = str_repeat('  ', $indent);
        fwrite(STDERR, "[DEBUG] {$pad}{$message}\n");
    }

    public function __toString(): string
    {
        return $this->prettyPrint();
    }

    public function prettyPrint(int $indent = 0): string
    {
        $pad = str_repeat('  ', $indent);
        $className = (new \ReflectionClass($this))->getShortName();
        $out = "{$pad}{$className} {\n";

        $ref = new \ReflectionObject($this);
        foreach ($ref->getProperties() as $prop) {
            $prop->setAccessible(true);
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

        $out .= "{$pad}}\n";
        return $out;
    }
}

?>