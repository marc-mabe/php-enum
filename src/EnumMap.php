<?php

namespace MabeEnum;

use SplObjectStorage;
use InvalidArgumentException;

/**
 * A map of enumerator keys of the given enumeration (EnumMap<T>)
 * based on SplObjectStorage
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMap extends SplObjectStorage
{
    /**
     * The classname of the enumeration type
     * @var string
     */
    private $enumeration;

    /**
     * Constructor
     * @param string $enumeration The classname of the enumeration type
     * @throws InvalidArgumentException
     */
    public function __construct($enumeration)
    {
        if (!is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(sprintf(
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
     * Get the current value
     * @return mixed
     */
    public function current()
    {
        return parent::getInfo();
    }

    /**
     * Get the current key
     * @return Enum|null
     */
    public function key()
    {
        return parent::current();
    }
}
