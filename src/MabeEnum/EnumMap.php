<?php

namespace MabeEnum;

use SplObjectStorage;
use InvalidArgumentException;
use RuntimeException;

/**
 * EnumMap implementation in base of SplObjectStorage
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMap extends SplObjectStorage
{

    /**
     * key()-behaviour: return the current iterator position
     */
    const KEY_AS_INDEX   = 1;

    /**
     * key()-behaviour: return the current enum name
     */
    const KEY_AS_NAME    = 2;

    /**
     * key()-behaviour: return the current enum value
     */
    const KEY_AS_VALUE   = 3;

    /**
     * key()-behaviour: return the current enum ordinal
     */
    const KEY_AS_ORDINAL = 4;

    /**
     * current()-behaviour: return the current enum object
     */
    const CURRENT_AS_ENUM    = 8;

    /**
     * current()-behaviour: return data mapped the current enum
     */
    const CURRENT_AS_DATA    = 16;

    /**
     * current()-behaviour: return the current enum name
     */
    const CURRENT_AS_NAME    = 24;

    /**
     * current()-behaviour: return the current enum value
     */
    const CURRENT_AS_VALUE   = 32;

    /**
     * current()-behaviour: return the current enum ordinal
     */
    const CURRENT_AS_ORDINAL = 40;

    /**
     * The classname of an enumeration this map is for
     * @var string
     */
    private $enumClass;

    /**
     * Flags to define behaviors
     * (Default = KEY_AS_INDEX | CURRENT_AS_ENUM)
     * @var int
     */
    private $flags = 9;

    /**
     * Constructor
     * @param string   $enumClass The classname of an enumeration the map is for
     * @param int|null $flags     Behaviour flags, see KEY_AS_* and CURRENT_AS_* constants
     * @throws InvalidArgumentException
     */
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

    /**
     * Get the classname of enumeration this map is for
     * @return string
     */
    public function getEnumClass()
    {
        return $this->enumClass;
    }

    /**
     * Set behaviour flags
     * see KEY_AS_* and CURRENT_AS_* constants
     * @param int $flags
     * @return void
     * @throws InvalidArgumentException On invalid or unsupported flags
     */
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

    /**
     * Get the behaviour flags
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Attach a new enumeration or overwrite an existing one
     * @param Enum|null|boolean|int|float|string $enum
     * @param mixed                              $data
     * @return void
     * @throws InvalidArgumentException On an invalid given enum
     */
    public function attach($enum, $data = null)
    {
        parent::attach($this->initEnum($enum), $data);
    }

    /**
     * Test if the given enumeration exists
     * @param Enum|null|boolean|int|float|string $enum
     * @return boolean
     */
    public function contains($enum)
    {
        try {
            return parent::contains($this->initEnum($enum));
        } catch (InvalidArgumentException $e) {
            // On an InvalidArgumentException the given argument can't be contained in this map
            return false;
        }
    }

    /**
     * Detach an enumeration
     * @param Enum|null|boolean|int|float|string $enum
     * @return void
     * @throws InvalidArgumentException On an invalid given enum
     */
    public function detach($enum)
    {
        parent::detach($this->initEnum($enum));
    }

    /**
     * Get a unique identifier for the given enumeration
     * @param Enum|scalar $enum
     * @return string
     * @throws InvalidArgumentException On an invalid given enum
     */
    public function getHash($enum)
    {
        // getHash is available since PHP 5.4
        return spl_object_hash($this->initEnum($enum));
    }

    /**
     * Test if the given enumeration exists
     * @param Enum|null|boolean|int|float|string $enum
     * @return boolean
     * @see contains()
     */
    public function offsetExists($enum)
    {
        return $this->contains($enum);
    }

    /**
     * Get mapped data for this given enum
     * @param Enum|null|boolean|int|float|string $enum
     * @return mixed
     * @throws InvalidArgumentException On an invalid given enum
     */
    public function offsetGet($enum)
    {
        return parent::offsetGet($this->initEnum($enum));
    }

    /**
     * Attach a new enumeration or overwrite an existing one
     * @param Enum|null|boolean|int|float|string $enum
     * @param mixed                              $data
     * @return void
     * @throws InvalidArgumentException On an invalid given enum
     * @see attach()
     */
    public function offsetSet($enum, $data = null)
    {
        parent::offsetSet($this->initEnum($enum), $data);
    }

    /**
     * Detach an existing enumeration
     * @param Enum|null|boolean|int|float|string $enum
     * @return void
     * @throws InvalidArgumentException On an invalid given enum
     * @see detach()
     */
    public function offsetUnset($enum)
    {
        parent::offsetUnset($this->initEnum($enum));
    }

    /**
     * Get the current item
     * The return value varied by the behaviour of the current flag
     * @return mixed
     */
    public function current()
    {
        switch ($this->flags & 120) {
            case self::CURRENT_AS_ENUM:
                return parent::current();
            case self::CURRENT_AS_DATA:
                return parent::getInfo();
            case self::CURRENT_AS_VALUE:
                return parent::current()->getValue();
            case self::CURRENT_AS_NAME:
                return parent::current()->getName();
            case self::CURRENT_AS_ORDINAL:
                return parent::current()->getOrdinal();
            default:
                throw new RuntimeException('Invalid current flag');
        }
    }

    /**
     * Get the current item-key
     * The return value varied by the behaviour of the key flag
     * @return null|boolean|int|float|string
     */
    public function key()
    {
        switch ($this->flags & 7) {
            case self::KEY_AS_INDEX:
                return parent::key();
            case self::KEY_AS_NAME:
                return parent::current()->getName();
            case self::KEY_AS_ORDINAL:
                return parent::current()->getOrdinal();
            case self::KEY_AS_VALUE:
                return parent::current()->getValue();
            default:
                throw new RuntimeException('Invalid key flag');
        }
    }

    /**
     * Initialize an enumeration
     * @param Enum|null|boolean|int|float|string $enum
     * @return Enum
     * @throws InvalidArgumentException On an invalid given enum
     */
    private function initEnum($enum)
    {
        // auto instantiate
        if (is_scalar($enum)) {
            $enumClass = $this->enumClass;
            return $enumClass::get($enum);
        }

        // allow only enums of the same type
        // (don't allow instance of)
        $enumClass = get_class($enum);
        if ($enumClass && strcasecmp($enumClass, $this->enumClass) === 0) {
            return $enum;
        }

        throw new InvalidArgumentException(sprintf(
            "The given enum of type '%s' isn't same as the required type '%s'",
            get_class($enum) ?: gettype($enum),
            $this->enumClass
        ));
    }
}
