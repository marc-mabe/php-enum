<?php

namespace MabeEnum;

use SplObjectStorage;
use InvalidArgumentException;
use RuntimeException;

class EnumMap extends SplObjectStorage
{

    const KEY_AS_INDEX   = 1;
    const KEY_AS_NAME    = 2;
    const KEY_AS_VALUE   = 3;
    const KEY_AS_ORDINAL = 4;
    const CURRENT_AS_ENUM    = 8;
    const CURRENT_AS_DATA    = 16;
    const CURRENT_AS_NAME    = 24;
    const CURRENT_AS_VALUE   = 32;
    const CURRENT_AS_ORDINAL = 40;

    private $enumClass;

    /**
     * Flags to define behaviors
     * (Default = KEY_AS_INDEX | CURRENT_AS_ENUM)
     * @var int
     */
    private $flags = 9;

    public function __construct($enumClass, $flags = null)
    {
        if (!is_subclass_of($enumClass, __NAMESPACE__ . '\Enum')) {
            throw new InvalidArgumentException(sprintf(
                "This EnumMap can handle subclasses of '%s' only",
                __NAMESPACE__ . '\Enum'
            ));
        }
        $this->enumClass = $enumClass;

        if ($flags !== null) {
            $this->setFlags($flags);
        }
    }

    public function getEnumClass()
    {
        return $this->enumClass;
    }

    public function setFlags($flags)
    {
        $flags = (int)$flags;

        $keyFlag = $flags & 7;
        if ($keyFlag > 4) {
            throw new InvalidArgumentException(
                "Unsupported flag given for key() behavior"
            );
        } elseif (!$keyFlag) {
            $keyFlag = $this->flags & 7;
        }
        

        $currentFlag = $flags & 56;
        if ($currentFlag > 40) {
            throw new InvalidArgumentException(
                "Unsupported flag given for current() behavior"
            );
        } elseif (!$currentFlag) {
            $currentFlag = $this->flags & 56;
        }

        $this->flags = $keyFlag | $currentFlag;
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
        try {
            $this->initEnum($enum);
            return parent::contains($enum);
        } catch (InvalidArgumentException $e) {
            // On an InvalidArgumentException the given argument can't be contained in this map
            return false;
        }
    }

    public function detach($enum)
    {
        $this->initEnum($enum);
        parent::detach($enum);
    }

    public function getHash($enum)
    {
        $this->initEnum($enum);

        // getHash is available since PHP 5.4
        return spl_object_hash($enum);
    }

    public function offsetExists($enum)
    {
        return $this->contains($enum);
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
        parent::offsetUnset($enum);
    }

    public function current()
    {
        switch ($this->flags & 120) {
            case self::CURRENT_AS_ENUM:
                return parent::current();
            case self::CURRENT_AS_DATA:
                return parent::getInfo();
            case self::CURRENT_AS_NAME:
                return parent::current()->getName();
            case self::CURRENT_AS_VALUE:
                return parent::current()->getValue();
            case self::CURRENT_AS_ORDINAL:
                return parent::current()->getOrdinal();
            default:
                throw new RuntimeException('Invalid current flag');
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
                throw new RuntimeException('Invalid key flag');
        }
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
