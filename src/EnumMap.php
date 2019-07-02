<?php

declare(strict_types=1);

namespace MabeEnum;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use UnexpectedValueException;

/**
 * A map of enumerators and data values (EnumMap<K extends Enum, V>).
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumMap implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The classname of the enumeration type
     * @var string
     */
    private $enumeration;

    /**
     * Internal map of ordinal number and data value
     * @var array
     */
    private $map = [];

    /**
     * Constructor
     * @param string $enumeration The classname of the enumeration type
     * @param null|iterable $map Initialize map
     * @throws InvalidArgumentException
     */
    public function __construct(string $enumeration, iterable $map = null)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                '%s can handle subclasses of %s only',
                 __CLASS__,
                Enum::class
            ));
        }
        $this->enumeration = $enumeration;

        if ($map) {
            $this->addIterable($map);
        }
    }

    /* write access (mutable) */

    /**
     * Adds the given enumerator (object or value) mapping to the specified data value.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @param mixed                                 $value
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see offsetSet()
     */
    public function add($enumerator, $value): void
    {
        $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
        $this->map[$ord] = $value;
    }

    /**
     * Adds the given iterable, mapping enumerators (objects or values) to data values.
     * @param iterable $map
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function addIterable(iterable $map): void
    {
        $innerMap = $this->map;
        foreach ($map as $enumerator => $value) {
            $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
            $innerMap[$ord] = $value;
        }
        $this->map = $innerMap;
    }

    /**
     * Removes the given enumerator (object or value) mapping.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see offsetUnset()
     */
    public function remove($enumerator): void
    {
        $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
        unset($this->map[$ord]);
    }

    /**
     * Removes the given iterable enumerator (object or value) mappings.
     * @param iterable $enumerators
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function removeIterable(iterable $enumerators): void
    {
        $map = $this->map;
        foreach ($enumerators as $enumerator) {
            $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
            unset($map[$ord]);
        }

        $this->map = $map;
    }

    /* write access (immutable) */

    /**
     * Creates a new map with the given enumerator (object or value) mapping to the specified data value added.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @param mixed                                 $value
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function with($enumerator, $value): self
    {
        $clone = clone $this;
        $clone->add($enumerator, $value);
        return $clone;
    }

    /**
     * Creates a new map with the given iterable mapping enumerators (objects or values) to data values added.
     * @param iterable $map
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function withIterable(iterable $map): self
    {
        $clone = clone $this;
        $clone->addIterable($map);
        return $clone;
    }

    /**
     * Create a new map with the given enumerator mapping removed.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function without($enumerator): self
    {
        $clone = clone $this;
        $clone->remove($enumerator);
        return $clone;
    }

    /**
     * Creates a new map with the given iterable enumerator (object or value) mappings removed.
     * @param iterable $enumerators
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function withoutIterable(iterable $enumerators): self
    {
        $clone = clone $this;
        $clone->removeIterable($enumerators);
        return $clone;
    }

    /* read access */

    /**
     * Get the classname of the enumeration type.
     * @return string
     */
    public function getEnumeration(): string
    {
        return $this->enumeration;
    }

    /**
     * Get the mapped data value of the given enumerator (object or value).
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return mixed
     * @throws InvalidArgumentException On an invalid given enumerator
     * @throws UnexpectedValueException If the given enumerator does not exist in this map
     * @see offsetGet()
     */
    public function get($enumerator)
    {
        $enumerator = ($this->enumeration)::get($enumerator);
        $ord = $enumerator->getOrdinal();
        if (!\array_key_exists($ord, $this->map)) {
            throw new UnexpectedValueException(sprintf(
                'Enumerator %s could not be found',
                \var_export($enumerator->getValue(), true)
            ));
        }

        return $this->map[$ord];
    }

    /**
     * Get a list of enumerator keys.
     * @return Enum[]
     */
    public function getKeys(): array
    {
        return \array_map([$this->enumeration, 'byOrdinal'], \array_keys($this->map));
    }

    /**
     * Get a list of mapped data values.
     * @return mixed[]
     */
    public function getValues(): array
    {
        return \array_values($this->map);
    }

    /**
     * Search for the given data value.
     * @param mixed $value
     * @param bool $strict Use strict type comparison
     * @return Enum|null The enumerator object of the first matching data value or NULL
     */
    public function search($value, bool $strict = false)
    {
        $ord = \array_search($value, $this->map, $strict);
        if ($ord !== false) {
            return ($this->enumeration)::byOrdinal($ord);
        }

        return null;
    }

    /**
     * Test if the given enumerator key (object or value) exists.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see offsetExists()
     */
    public function has($enumerator): bool
    {
        try {
            $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
            return \array_key_exists($ord, $this->map);
        } catch (InvalidArgumentException $e) {
            // An invalid enumerator can't be contained in this map
            return false;
        }
    }

    /**
     * Test if the given enumerator key (object or value) exists.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see offsetExists()
     * @see has()
     * @deprecated Will trigger deprecation warning in last 4.x and removed in 5.x
     */
    public function contains($enumerator): bool
    {
        return $this->has($enumerator);
    }

    /* ArrayAccess */

    /**
     * Test if the given enumerator key (object or value) exists and is not NULL
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see contains()
     */
    public function offsetExists($enumerator): bool
    {
        try {
            return isset($this->map[($this->enumeration)::get($enumerator)->getOrdinal()]);
        } catch (InvalidArgumentException $e) {
            // An invalid enumerator can't be an offset of this map
            return false;
        }
    }

    /**
     * Get the mapped data value of the given enumerator (object or value).
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return mixed The mapped date value of the given enumerator or NULL
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see get()
     */
    public function offsetGet($enumerator)
    {
        try {
            return $this->get($enumerator);
        } catch (UnexpectedValueException $e) {
            return null;
        }
    }

    /**
     * Adds the given enumerator (object or value) mapping to the specified data value.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @param mixed                                 $value
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see add()
     */
    public function offsetSet($enumerator, $value = null): void
    {
        $this->add($enumerator, $value);
    }

    /**
     * Removes the given enumerator (object or value) mapping.
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see remove()
     */
    public function offsetUnset($enumerator): void
    {
        $this->remove($enumerator);
    }

    /* IteratorAggregate */

    /**
     * Get a new Iterator.
     *
     * @return Iterator Iterator<K extends Enum, V>
     */
    public function getIterator(): Iterator
    {
        $map = $this->map;
        foreach ($map as $ordinal => $value) {
            yield ($this->enumeration)::byOrdinal($ordinal) => $value;
        }
    }

    /* Countable */

    /**
     * Count the number of elements
     *
     * @return int
     */
    public function count(): int
    {
        return \count($this->map);
    }

    /**
     * Tests if the map is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->map);
    }
}
