<?php

class MabeEnum_EnumMap implements Iterator, Countable
{
    private $enums  = array();
    private $values = array();

    private $enumClass;

    public function __construct($enumClass)
    {
        $reflectionClass = new ReflectionClass($enumClass);
        if (!reflectionClass->isInstanceOf('MabeEnum_Enum')) {
            throw new InvalidArgumentException("'{$enumClass}' have to be an instance of 'MabeEnum_Enum'");
        }

        $this->enumClass  = $enumClass;
    }

    public function attach($enum, $value)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = new $this->enumClass($enum);
        }

        if ($this->contains($enum)) {
            throw new RuntimeException("'{$enum}' already attached to map");
        }

        $ordinal = $enum->getOrdinal();
        $this->values[$ordinal] = $value;
        $this->enums[$ordinal]  = $enum;
    }

    public function get($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = new $this->enumClass($enum);
        }

        if (!$this->contains($enum)) {
            throw new RuntimeException("'{$enum}' not attached to map");
        }

        return $this->values[$enum->getOrdinal()];
    }

    public function contains($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = new $this->enumClass($enum);
        }

        return array_key_exists($enum->getOrdinal(), $this->values);
    }

    public function detach($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = new $this->enumClass($enum);
        }

        if (!$this->contains($enum)) {
            throw new RuntimeException("'{$enum}' not attached to map");
        }

        $ordinal = $enum->getOrdinal();
        unset($this->values[$ordinal], $this->enums[$ordinal]);
    }

    /* Iterator */

    

    /* Countable */

    public function count()
    {
        return count($this->map);
    }
}
