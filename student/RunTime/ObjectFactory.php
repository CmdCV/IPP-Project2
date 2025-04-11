<?php

namespace IPP\Student\RunTime;

use IPP\Student\Exceptions\ValueException;
use IPP\Student\RunTime\ObjectInstance;
use IPP\Student\Classes\SolClass;

class ObjectFactory
{
    /** @var array<string, SolClass> */
    private static array $classes = [];

    private static ?ObjectInstance $true = null;
    private static ?ObjectInstance $false = null;
    private static ?ObjectInstance $nil = null;

    public static function registerClass(SolClass $class): void
    {
        self::$classes[$class->getName()] = $class;
    }

    public static function getClass(string $name): SolClass
    {
        return self::$classes[$name]
            ?? throw new ValueException("Class '$name' is not defined");
    }

    public static function integer(int $value): ObjectInstance
    {
        $class = self::getClass('Integer');
        $instance = $class->instantiate();
        $instance->setAttribute('__value', $value); // interní atribut
        return $instance;
    }

    public static function string(string $value): ObjectInstance
    {
        $class = self::getClass('String');
        $instance = $class->instantiate();
        $instance->setAttribute('__value', $value);
        return $instance;
    }

    public static function true(): ObjectInstance
    {
        if (!self::$true) {
            self::$true = self::getClass('True')->instantiate();
        }
        return self::$true;
    }

    public static function false(): ObjectInstance
    {
        if (!self::$false) {
            self::$false = self::getClass('False')->instantiate();
        }
        return self::$false;
    }

    public static function nil(): ObjectInstance
    {
        if (!self::$nil) {
            self::$nil = self::getClass('Nil')->instantiate();
        }
        return self::$nil;
    }

    public static function classReference(string $name): ObjectInstance
    {
        // pro výrazy jako `String from: ...`
        $class = self::getClass($name);
        return new ClassReference($class);
    }
}
