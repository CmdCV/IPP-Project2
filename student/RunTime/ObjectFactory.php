<?php

namespace IPP\Student\RunTime;

use IPP\Student\Exceptions\ValueException;
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

    /**
     * @throws ValueException
     */
    public static function getClass(string $name): SolClass
    {
        return self::$classes[$name]
            ?? throw new ValueException("Class '$name' is not defined");
    }

    /**
     * @throws ValueException
     */
    public static function integer(int $value): ObjectInstance
    {
        $class = self::getClass('Integer');
        $instance = $class->instantiate();
        $instance->setAttribute('__value', $value);
        return $instance;
    }

    /**
     * @throws ValueException
     */
    public static function string(string $value): ObjectInstance
    {
        $class = self::getClass('String');
        $instance = $class->instantiate();
        $instance->setAttribute('__value', $value);
        return $instance;
    }

    /**
     * @throws ValueException
     */
    public static function true(): ObjectInstance
    {
        if (!self::$true) {
            self::$true = self::getClass('True')->instantiate();
        }
        return self::$true;
    }

    /**
     * @throws ValueException
     */
    public static function false(): ObjectInstance
    {
        if (!self::$false) {
            self::$false = self::getClass('False')->instantiate();
        }
        return self::$false;
    }

    /**
     * @throws ValueException
     */
    public static function nil(): ObjectInstance
    {
        if (!self::$nil) {
            self::$nil = self::getClass('Nil')->instantiate();
        }
        return self::$nil;
    }

    /**
     * @throws ValueException
     */
    public static function classReference(string $name): ObjectInstance
    {
        $class = self::getClass($name);
        return new ClassReference($class);
    }
}
