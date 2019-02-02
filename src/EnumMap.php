<?php

namespace MabeEnum;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use OutOfBoundsException;
use SeekableIterator;
use UnexpectedValueException;

/**
 * A map of enumerators (EnumMap<T>) and mixed values.
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumMap implements ArrayAccess, Countable, SeekableIterator
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
     * Get a list of map keys
     * @return Enum[]
     */
    public function getKeys()
    {
        return \array_map([$this->enumeration, 'byOrdinal'], $this->ordinals);
    }

    /**
     * Get a list of map values
     * @return mixed[]
     */
    public function getValues()
    {
        return \array_values($this->map);
    }

    /**
     * Search for the given value
     * @param mixed $value
     * @param bool $strict Use strict type comparison
     * @return Enum|null The found key or NULL
     */
    public function search($value, $strict = false)
    {
        $ord = \array_search($value, $this->map, $strict);
        if ($ord !== false) {
            $enumeration = $this->enumeration;
            return $enumeration::byOrdinal($ord);
        }

        return null;
    }

    /**
     * Test if the given enumerator exists
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see offsetExists
     */
    public function contains($enumerator)
    {
        try {
            $enumeration = $this->enumeration;
            $ord  = $enumeration::get($enumerator)->getOrdinal();
            return array_key_exists($ord, $this->map);
        } catch (InvalidArgumentException $e) {
            // An invalid enumerator can't be contained in this map
            return false;
        }
    }

    /**
     * Test if the given enumerator key exists and is not NULL
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see contains
     */
    public function offsetExists($enumerator)
    {
        try {
            $enumeration = $this->enumeration;
            $ord  = $enumeration::get($enumerator)->getOrdinal();
            return isset($this->map[$ord]);
        } catch (InvalidArgumentException $e) {
            // An invalid enumerator can't be an offset of this map
            return false;
        }
    }

    /**
     * Get mapped data for the given enumerator
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return mixed
     * @throws InvalidArgumentException On an invalid given enumerator
     * @throws UnexpectedValueException If the given enumerator does not exist in this map
     */
    public function offsetGet($enumerator)
    {
        $enumeration = $this->enumeration;
        $enumerator  = $enumeration::get($enumerator);
        $ord = $enumerator->getOrdinal();
        if (!isset($this->map[$ord]) && !array_key_exists($ord, $this->map)) {
            throw new UnexpectedValueException(sprintf(
                'Enumerator %s could not be found',
                \var_export($enumerator->getValue(), true)
            ));
        }

        return $this->map[$ord];
    }

    /**
     * Attach a new enumerator or overwrite an existing one
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @param mixed                                 $value
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see attach()
     */
    public function offsetSet($enumerator, $value = null)
    {
        $enumeration = $this->enumeration;
        $ord = $enumeration::get($enumerator)->getOrdinal();

        if (!array_key_exists($ord, $this->map)) {
            $this->ordinals[] = $ord;
        }
        $this->map[$ord] = $value;
    }

    /**
     * Detach an existing enumerator
     * @param Enum|null|bool|int|float|string|array $enumerator
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
            $this->ordinals = \array_values($this->ordinals);
        }
    }

    /**
     * Seeks to the given iterator position.
     * @param int $pos
     * @throws OutOfBoundsException On an invalid position
     */
    public function seek($pos)
    {
        $pos = (int)$pos;
        if (!isset($this->ordinals[$pos])) {
            throw new OutOfBoundsException("Position {$pos} not found");
        }

        $this->pos = $pos;
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
     * @return bool
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
