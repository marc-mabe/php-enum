<?php

namespace MabeEnum;

use SplObjectStorage;
use InvalidArgumentException;
use RuntimeException;

/**
 * EnumMap implementation in base of SplObjectStorage
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMap extends SplObjectStorage
{

    /**
     * key()-behaviour: return the iterator position
     */
    const KEY_AS_INDEX   = 1;

    /**
     * key()-behaviour: return the name of the current element
     */
    const KEY_AS_NAME    = 2;

    /**
     * key()-behaviour: return the value of the current element
     */
    const KEY_AS_VALUE   = 3;

    /**
     * key()-behaviour: return the ordinal number of the current element
     */
    const KEY_AS_ORDINAL = 4;

    /**
     * current()-behaviour: return the instance of the enumerator
     */
    const CURRENT_AS_ENUM    = 8;

    /**
     * current()-behaviour: return the data mapped the enumerator
     */
    const CURRENT_AS_DATA    = 16;

    /**
     * current()-behaviour: return the name of the enumerator
     */
    const CURRENT_AS_NAME    = 24;

    /**
     * current()-behaviour: return the value of the enumerator
     */
    const CURRENT_AS_VALUE   = 32;

    /**
     * current()-behaviour: return the ordinal number of the enumerator
     */
    const CURRENT_AS_ORDINAL = 40;

    /**
     * The classname of the enumeration type
     * @var string
     */
    private $enumeration;

    /**
     * Flags to define behaviors
     * (Default = KEY_AS_INDEX | CURRENT_AS_ENUM)
     * @var int
     */
    private $flags = 9;

    /**
     * Constructor
     * @param string   $enumeration The classname of the enumeration type
     * @param int|null $flags       Behaviour flags, see KEY_AS_* and CURRENT_AS_* constants
     * @throws InvalidArgumentException
     */
    public function __construct($enumeration, $flags = null)
    {
        if (!is_subclass_of($enumeration, __NAMESPACE__ . '\Enum')) {
            throw new InvalidArgumentException(sprintf(
                "This EnumMap can handle subclasses of '%s' only",
                __NAMESPACE__ . '\Enum'
            ));
        }
        $this->enumeration = $enumeration;

        if ($flags !== null) {
            $this->setFlags($flags);
        }
    }

    /**
     * Get the classname of the enumeration
     * @return string
     */
    public function getEnumeration()
    {
        return $this->enumeration;
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
     * Attach a new enumerator or overwrite an existing one
     * @param Enum|null|boolean|int|float|string $enumerator
     * @param mixed                              $data
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function attach($enumerator, $data = null)
    {
        $enumeration = $this->enumeration;
        parent::attach($enumeration::get($enumerator), $data);
    }

    /**
     * Test if the given enumerator exists
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return boolean
     */
    public function contains($enumerator)
    {
        try {
            $enumeration = $this->enumeration;
            return parent::contains($enumeration::get($enumerator));
        } catch (InvalidArgumentException $e) {
            // On an InvalidArgumentException the given argument can't be contained in this map
            return false;
        }
    }

    /**
     * Detach an enumerator
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function detach($enumerator)
    {
        $enumeration = $this->enumeration;
        parent::detach($enumeration::get($enumerator));
    }

    /**
     * Get a unique identifier for the given enumerator
     * @param Enum|scalar $enumerator
     * @return string
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function getHash($enumerator)
    {
        // getHash is available since PHP 5.4
        $enumeration = $this->enumeration;
        return spl_object_hash($enumeration::get($enumerator));
    }

    /**
     * Test if the given enumerator exists
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return boolean
     * @see contains()
     */
    public function offsetExists($enumerator)
    {
        return $this->contains($enumerator);
    }

    /**
     * Get mapped data for the given enumerator
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return mixed
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function offsetGet($enumerator)
    {
        $enumeration = $this->enumeration;
        return parent::offsetGet($enumeration::get($enumerator));
    }

    /**
     * Attach a new enumerator or overwrite an existing one
     * @param Enum|null|boolean|int|float|string $enumerator
     * @param mixed                              $data
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see attach()
     */
    public function offsetSet($enumerator, $data = null)
    {
        $enumeration = $this->enumeration;
        parent::offsetSet($enumeration::get($enumerator), $data);
    }

    /**
     * Detach an existing enumerator
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see detach()
     */
    public function offsetUnset($enumerator)
    {
        $enumeration = $this->enumeration;
        parent::offsetUnset($enumeration::get($enumerator));
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
}
