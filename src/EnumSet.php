<?php

namespace MabeEnum;

use Iterator;
use Countable;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * EnumSet implementation in base of an integer bit set
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSet implements Iterator, Countable
{
    /**
     * The classname of the Enumeration
     * @var string
     */
    private $enumeration;

    /**
     * BitSet of all attached enumerations
     * @var int
     */
    private $bitset = 0;

    /**
     * Ordinal number of current iterator position
     * @var int
     */
    private $ordinal = 0;

    /**
     * Highest possible ordinal number
     * @var int
     */
    private $ordinalMax = 0;

    /**
     * Constructor
     *
     * @param string $enumeration The classname of the enumeration
     * @throws InvalidArgumentException
     */
    public function __construct($enumeration)
    {
        if (!is_subclass_of($enumeration, __NAMESPACE__ . '\Enum')) {
            throw new InvalidArgumentException(sprintf(
                "This EnumSet can handle subclasses of '%s' only",
                __NAMESPACE__ . '\Enum'
            ));
        }

        $this->enumeration = $enumeration;
        $this->ordinalMax  = count($enumeration::getConstants());

        if (PHP_INT_SIZE * 8 < $this->ordinalMax) {
            throw new OutOfRangeException(sprintf(
                "Your system can handle up to %u enumerators within an EnumSet"
                . " but the given enumeration '%s' has defined %u enumerators",
                PHP_INT_SIZE * 8,
                $enumeration,
                $this->ordinalMax
            ));
        }
    }

    /**
     * Get the classname of enumeration this set is for
     * @return string
     * @deprecated Please use getEnumeration() instead
     */
    public function getEnumClass()
    {
        return $this->getEnumeration();
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
     * Attach a new enumerator or overwrite an existing one
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function attach($enumerator)
    {
        $enumeration = $this->enumeration;
        $this->bitset |= 1 << $enumeration::get($enumerator)->getOrdinal();
    }

    /**
     * Detach the given enumerator
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function detach($enumerator)
    {
        $enumeration = $this->enumeration;
        $this->bitset &= ~(1 << $enumeration::get($enumerator)->getOrdinal());
    }

    /**
     * Test if the given enumerator was attached
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return boolean
     */
    public function contains($enumerator)
    {
        $enumeration = $this->enumeration;
        return (bool)($this->bitset & (1 << $enumeration::get($enumerator)->getOrdinal()));
    }

    /* Iterator */

    /**
     * Get the current enumerator
     * @return Enum|null Returns the current enumerator or NULL on an invalid iterator position
     */
    public function current()
    {
        if ($this->valid()) {
            $enumeration = $this->enumeration;
            return $enumeration::getByOrdinal($this->ordinal);
        }

        return null;
    }

    /**
     * Get the ordinal number of the current iterator position
     * @return int
     */
    public function key()
    {
        return $this->ordinal;
    }

    /**
     * Go to the next iterator position
     * @return void
     */
    public function next()
    {
        if ($this->ordinal !== $this->ordinalMax) {
            do {
                if (++$this->ordinal === $this->ordinalMax) {
                    return;
                }
            } while (!($this->bitset & (1 << $this->ordinal)));
        }
    }

    /**
     * Go to the first iterator position
     * @return void
     */
    public function rewind()
    {
        $this->ordinal = -1;
        do {
            if (++$this->ordinal === $this->ordinalMax) {
                return;
            }
        } while (!($this->bitset & (1 << $this->ordinal)));
    }

    /**
     * Test if the iterator in a valid state
     * @return boolean
     */
    public function valid()
    {
        return $this->bitset & (1 << $this->ordinal) && $this->ordinal !== $this->ordinalMax;
    }

    /* Countable */

    /**
     * Count the number of elements
     * @return int
     */
    public function count()
    {
        $cnt = 0;
        $ord = 0;
        do {
            if ($this->bitset & (1 << $ord++)) {
                ++$cnt;
            }
        } while ($ord !== $this->ordinalMax);

        return $cnt;
    }
}
