<?php

class MabeEnum_EnumMap implements Iterator, Countable
{
    private $enums  = array();
    private $values = array();
    private $position = 0;
    private $enumClass;

    public function __construct($enumClass)
    {
        $this->enumClass = $enumClass;
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

    public function currentValue()
    {
        return $this->values[$this->position];
    }

    public function currentEnum()
    {
        return $this->enums[$this->position];
    }

    public function currentPosition()
    {
        return $this->position;
    }

    /* Iterator */

    public function current()
    {
        return $this->currentValue();
    }

    public function key()
    {
        return $this->currentEnum()->getValue();
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return ($this->position < $this->count());
    }

    public function rewind()
    {
        $this->position = 0;
    }

    /* Countable */

    public function count()
    {
        return count($this->enums);
    }
}
