<?php

namespace MabeEnum;

use SplObjectStorage;
use InvalidArgumentException;

class EnumMap extends SplObjectStorage
{

    const KEY_AS_INDEX   = 1;
    const KEY_AS_NAME    = 2;
    const KEY_AS_VALUE   = 3;
    const KEY_AS_ORDINAL = 4;
    const CURRENT_AS_ENUM    = 8;
    const CURRENT_AS_DATA    = 16;
    const CURRENT_AS_NAME    = 24;
    const CURRENT_AS_VALUE   = 56;
    const CURRENT_AS_ORDINAL = 120;

    private $enumClass;
    private $flags;

    public function __construct($enumClass, $flags = null)
    {
        if (!is_subclass_of($enumClass, __NAMESPACE__ . '\Enum')) {
            throw new InvalidArgumentException(sprintf(
                "This EnumMap can handle subclasses of '%s' only",
                __NAMESPACE__ . '\Enum'
            ));
        }
        $this->enumClass = $enumClass;

        if ($flags === null) {
            $flags = self::KEY_AS_INDEX | self::CURRENT_AS_ENUM;
        }
        $this->setFlags($flags);
    }

    public function getEnumClass()
    {
        return $this->enumClass;
    }

    public function setFlags($flags)
    {
        $flags = (int)$flags;

        $keyFlags = $flags & 7;
        if ($keyFlags < 1 || $keyFlags > 4) {
            throw new InvalidArgumentException(
                "Flags have to contain one of the 'KEY_AS_*' constants"
            );
        }

        $currentFlags = $flags & 120;
        if ($currentFlags < 8 || $currentFlags > 120) {
            throw new InvalidArgumentException(
                "Flags have to contain one of the 'CURRENT_AS_*' constants"
            );
        }

        $this->flags = $flags;
    }

    public function getFlags()
    {
        return $this->flags;
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

    public function current()
    {
        switch ($this->flags & 120) {
            case self::CURRENT_AS_ENUM:
                return parent::current();
            case self::CURRENT_AS_DATA:
                return parent::getInfo();
            case self::CURRENT_AS_VALUE:
                return parent::current()->getValue();
            default:
                throw new RuntimeException(
                    'Invalid current flags'
                );
        }
    }

    public function key()
    {
        switch ($this->flags & 7) {
            case self::KEY_AS_INDEX:
                return parent::key();
            case self::KEY_AS_NAME:
                return parent::current()->getName();
            case self::KEY_AS_VALUE:
                return parent::current()->getValue();
            case self::KEY_AS_ORDINAL:
                return parent::current()->getOrdinal();
            default:
                throw new RuntimeException(
                    'Invalid key flags'
                );
        }
    }
}
