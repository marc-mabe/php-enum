<?php

namespace MabeEnum;

use SplObjectStorage;

class EnumMap extends SplObjectStorage
{

    public function __construct($enumClass)
    {
        $this->enumClass = $enumClass;
    }

    public function attach($enum, $data = null)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        parent::attach($enum, $data);
    }

    public function contains($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        return parent::contains($enum);
    }

    public function detach($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        parent::detach($enum);
    }

    public function getHash($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        return parent::getHash($enum);
    }

    public function offsetExists($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        return parent::offsetExists($enum);
    }

    public function offsetGet($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        return parent::offsetGet($enum);
    }

    public function offsetSet($enum, $data = null)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        parent::offsetSet($enum, $data);
    }

    public function offsetUnset($enum)
    {
        if (!($enum instanceof $this->enumClass)) {
            $enum = call_user_func(array($this->enumClass, 'get'), $enum);
        }

        parent::offsetUnset($enum, $data);
    }
}
