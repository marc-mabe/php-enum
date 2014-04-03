<?php

namespace MabeEnum;

use Iterator;
use Countable;
use InvalidArgumentException;

/**
 * EnumSet implementation in base of SplObjectStorage
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSet implements Iterator, Countable
{
    /**
     * Flag for a unique set of enumerations
     */
    const UNIQUE  = 1;

    /**
     * Flag for an ordered set of enumerations by ordinal
     */
    const ORDERED = 2;

    /**
     * @var string
     */
    private $enumClass;

    /**
     * @var array
     */
    private $list      = array();

    /**
     * @var int
     */
    private $index     = 0;

    /**
     * @var int
     */
    private $flags     = self::UNIQUE;

    /**
     * Constructor
     *
     * @param string   $enumClass The classname of an enumeration the map is for
     * @param null|int $flags     Flags to define behaviours
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
            $this->flags = (int) $flags;
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
     * Get flags of defined behaviours
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Attach a new enumeration or overwrite an existing one
     * @param Enum|null|boolean|int|float|string $enum
     * @return void
     * @throws InvalidArgumentException On an invalid given enum
     */
    public function attach($enum)
    {
        $enum    = $this->initEnum($enum);
        $ordinal = $enum->getOrdinal();

        if (!($this->flags & self::UNIQUE) || !in_array($ordinal, $this->list, true)) {
            $this->list[] = $ordinal;

            if ($this->flags & self::ORDERED) {
                sort($this->list);
            }
        }
    }

    /**
     * Test if the given enumeration exists
     * @param Enum|null|boolean|int|float|string $enum
     * @return boolean
     */
    public function contains($enum)
    {
        $enum = $this->initEnum($enum);
        return in_array($enum->getOrdinal(), $this->list, true);
    }

    /**
     * Detach all enumerations same as the given enum
     * @param Enum|null|boolean|int|float|string $enum
     * @return void
     * @throws InvalidArgumentException On an invalid given enum
     */
    public function detach($enum)
    {
        $enum = $this->initEnum($enum);

        while (($index = array_search($enum->getOrdinal(), $this->list, true)) !== false) {
            unset($this->list[$index]);
        }

        // reset index positions to have a real list
        $this->list = array_values($this->list);
    }

    /* Iterator */

    /**
     * Get the current Enum
     * @return Enum|null Returns the current Enum or NULL on an invalid iterator position
     */
    public function current()
    {
        if (!isset($this->list[$this->index])) {
            return null;
        }

        $enumClass = $this->enumClass;
        return $enumClass::getByOrdinal($this->list[$this->index]);
    }

    /**
     * Get the current iterator position
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        ++$this->index;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        return isset($this->list[$this->index]);
    }

    /* Countable */

    public function count()
    {
        return count($this->list);
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
