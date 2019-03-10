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
 * A map of enumerators (EnumMap<T>) and mixed values.
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
     * Internal map of ordinal number and value
     * @var array
     */
    private $map = [];

    /**
     * Constructor
     * @param string $enumeration The classname of the enumeration type
     * @throws InvalidArgumentException
     */
    public function __construct(string $enumeration)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                '%s can handle subclasses of %s only',
                 __CLASS__,
                Enum::class
            ));
        }
        $this->enumeration = $enumeration;
    }

    /**
     * Get the classname of the enumeration
     * @return string
     */
    public function getEnumeration(): string
    {
        return $this->enumeration;
    }

    /**
     * Get a list of map keys
     * @return Enum[]
     */
    public function getKeys(): array
    {
        return \array_map([$this->enumeration, 'byOrdinal'], \array_keys($this->map));
    }

    /**
     * Get a list of map values
     * @return mixed[]
     */
    public function getValues(): array
    {
        return \array_values($this->map);
    }

    /**
     * Search for the given value
     * @param mixed $value
     * @param bool $strict Use strict type comparison
     * @return Enum|null The found key or NULL
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
     * Test if the given enumerator exists
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see offsetExists
     */
    public function contains($enumerator): bool
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
     * Test if the given enumerator key exists and is not NULL
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     * @see contains
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
     * Get mapped data for the given enumerator
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return mixed
     * @throws InvalidArgumentException On an invalid given enumerator
     * @throws UnexpectedValueException If the given enumerator does not exist in this map
     */
    public function offsetGet($enumerator)
    {
        $enumerator = ($this->enumeration)::get($enumerator);
        $ord = $enumerator->getOrdinal();
        if (!isset($this->map[$ord]) && !\array_key_exists($ord, $this->map)) {
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
    public function offsetSet($enumerator, $value = null): void
    {
        $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
        $this->map[$ord] = $value;
    }

    /**
     * Detach an existing enumerator
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     * @see detach()
     */
    public function offsetUnset($enumerator): void
    {
        $ord = ($this->enumeration)::get($enumerator)->getOrdinal();
        unset($this->map[$ord]);
    }

    /**
     * Get a new Iterator.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        $map = $this->map;
        foreach ($map as $ordinal => $value) {
            yield ($this->enumeration)::byOrdinal($ordinal) => $value;
        }
    }

    /**
     * Count the number of elements
     *
     * @return int
     */
    public function count(): int
    {
        return \count($this->map);
    }
}
