<?php

namespace MabeEnum;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use UnexpectedValueException;

/**
 * A map of enumerators (EnumMap<T>) and mixed values.
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMap implements ArrayAccess, Countable, Iterator
{
    /**
     * The classname of the enumeration type
     * @var string
     */
    private $enumeration;

    /**
     * Internal map of ordinal number and value
     * @var array
     */
    private $map = [];

    /**
     * List of ordinal numbers
     * @var int[]
     */
    private $ordinals = [];

    /**
     * Current iterator position
     * @var int
     */
    private $pos = 0;

    /**
     * Constructor
     * @param string $enumeration The classname of the enumeration type
     * @throws InvalidArgumentException
     */
    public function __construct($enumeration)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                "This EnumMap can handle subclasses of '%s' only",
                Enum::class
            ));
        }
        $this->enumeration = $enumeration;
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
     * @param mixed                              $data
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function attach($enumerator, $data = null)
    {
        return $this->offsetSet($enumerator, $data);
    }

    /**
     * Test if the given enumerator exists
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return boolean
     */
    public function contains($enumerator)
    {
        return $this->offsetExists($enumerator);
    }

    /**
     * Detach an enumerator
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function detach($enumerator)
    {
        $this->offsetUnset($enumerator);
    }

    /**
     * Test if the given enumerator exists
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return boolean
     * @see contains()
     */
    public function offsetExists($enumerator)
    {
        try {
            $enumeration = $this->enumeration;
            $ord  = $enumeration::get($enumerator)->getOrdinal();
            return isset($this->map[$ord]);
        } catch (InvalidArgumentException $e) {
            // An invalid enumerator can't be contained in this map
            return false;
        }
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
        $ord = $enumeration::get($enumerator)->getOrdinal();
        if (!isset($this->map[$ord])) {
            throw new UnexpectedValueException(\sprintf(
                "Enumerator '%s' could not be found",
                \is_object($enumerator) ? $enumerator->getValue() : $enumerator
            ));
        }

        return $this->map[$ord];
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
        $ord = $enumeration::get($enumerator)->getOrdinal();

        if (!isset($this->map[$ord])) {
            $this->ordinals[] = $ord;
        }
        $this->map[$ord] = $data;
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
        $ord = $enumeration::get($enumerator)->getOrdinal();

        if (($idx = \array_search($ord, $this->ordinals, true)) !== false) {
            unset($this->map[$ord], $this->ordinals[$idx]);
        }
    }

    /**
     * Get the current value
     * @return mixed
     */
    public function current()
    {
        if (!isset($this->ordinals[$this->pos])) {
            return null;
        }

        return $this->map[$this->ordinals[$this->pos]];
    }

    /**
     * Get the current key
     * @return Enum|null
     */
    public function key()
    {
        if (!isset($this->ordinals[$this->pos])) {
            return null;
        }

        $enumeration = $this->enumeration;
        return $enumeration::byOrdinal($this->ordinals[$this->pos]);
    }

    /**
     * Reset the iterator position to zero.
     * @return void
     */
    public function rewind()
    {
        $this->pos = 0;
    }

    /**
     * Increment the iterator position by one.
     * @return void
     */
    public function next()
    {
        ++$this->pos;
    }

    /**
     * Test if the iterator is in a valid state
     * @return boolean
     */
    public function valid()
    {
        return isset($this->ordinals[$this->pos]);
    }

    /**
     * Count the number of elements
     *
     * @return int
     */
    public function count()
    {
        return \count($this->ordinals);
    }
}
