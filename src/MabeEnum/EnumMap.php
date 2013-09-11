<?php

namespace MabeEnum;

use SplObjectStorage;
use InvalidArgumentException;

class EnumMap extends SplObjectStorage
{

    public function __construct($enumClass)
    {
        if (!is_subclass_of($enumClass, 'MabeEnum\Enum')) {
            throw new InvalidArgumentException(
                "This EnumMap can only handle subclasses of 'MabeEnum\Enum'"
            );
        }
        $this->enumClass = $enumClass;
    }

    public function attach($enum, $data = null)
    {
        $this->initEnum($enum);
        parent::attach($enum, $data);
    }

    public function contains($enum)
    {
        $this->initEnum($enum);
        return parent::contains($enum);
    }

    public function detach($enum)
    {
        $this->initEnum($enum);
        parent::detach($enum);
    }

    public function getHash($enum)
    {
        $this->initEnum($enum);
        return parent::getHash($enum);
    }

    public function offsetExists($enum)
    {
        $this->initEnum($enum);
        return parent::offsetExists($enum);
    }

    public function offsetGet($enum)
    {
        $this->initEnum($enum);
        return parent::offsetGet($enum);
    }

    public function offsetSet($enum, $data = null)
    {
        $this->initEnum($enum);
        parent::offsetSet($enum, $data);
    }

    public function offsetUnset($enum)
    {
        $this->initEnum($enum);
        parent::offsetUnset($enum, $data);
    }

    private function initEnum(&$enum)
    {
        // auto instantiate
        if (is_scalar($enum)) {
            $enumClass = $this->enumClass;
            $enum      = $enumClass::get($enum);
            return;
        }

        // allow only enums of the same type
        // (don't allow instance of)
        $enumClass = get_class($enum);
        if ($enumClass && strcasecmp($enumClass, $this->enumClass) === 0) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            "The given enum of type '%s' isn't same as the required type '%s'",
            get_class($enum) ?: gettype($enum),
            $this->enumClass
        ));
    }
}
