<?php

namespace MabeEnum;

use Iterator;
use Countable;
use InvalidArgumentException;

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
     * BitSet of all attached enumerations in little endian
     * @var string
     */
    private $bitset;

    /**
     * Ordinal number of current iterator position
     * @var int
     */
    private $ordinal = 0;

    /**
     * Highest possible ordinal number
     * @var int
     */
    private $ordinalMax;

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
        
        // init the bitset with zeros
        $this->bitset = str_repeat("\0", ceil($this->ordinalMax / 8));
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
        $this->setBit($enumeration::get($enumerator)->getOrdinal());
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
        $this->unsetBit($enumeration::get($enumerator)->getOrdinal());
    }

    /**
     * Test if the given enumerator was attached
     * @param Enum|null|boolean|int|float|string $enumerator
     * @return boolean
     */
    public function contains($enumerator)
    {
        $enumeration = $this->enumeration;
        return $this->getBit($enumeration::get($enumerator)->getOrdinal());
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
     * Go to the next valid iterator position.
     * If no valid iterator position is found the iterator position will be the last possible + 1.
     * @return void
     */
    public function next()
    {
        do {
            if (++ $this->ordinal >= $this->ordinalMax) {
                $this->ordinal = $this->ordinalMax;
                return;
            }
        } while (!$this->getBit($this->ordinal));
    }

    /**
     * Go to the first valid iterator position.
     * If no valid iterator position in found the iterator position will be 0.
     * @return void
     */
    public function rewind()
    {
        if (trim($this->bitset, "\0") !== '') {
            $this->ordinal = -1;
            $this->next();
        } else {
            $this->ordinal = 0;
        }
    }

    /**
     * Test if the iterator in a valid state
     * @return boolean
     */
    public function valid()
    {
        return $this->ordinal !== $this->ordinalMax && $this->getBit($this->ordinal);
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
            if ($this->getBit($ord++)) {
                ++$cnt;
            }
        } while ($ord !== $this->ordinalMax);

        return $cnt;
    }

    /**
     * Get the binary bitset
     * 
     * @return string Returns the binary bitset in big-endian order
     */
    public function getBitset()
    {
        return strrev($this->bitset);
    }

    /**
     * Set the bitset.
     * NOTE: It resets the current position of the iterator
     * 
     * @param string $bitset The binary bitset in big-endian order
     * @return void
     * @throws InvalidArgumentException On a non string is given as Parameter
     */
    public function setBitset($bitset)
    {
        if (! is_string($bitset)) {
            throw new InvalidArgumentException("bitset must be a string");
        }
        
        $bitset = strrev($bitset);
        $size   = ceil($this->ordinalMax / 8);
        $sizeIn = strlen($bitset);
        
        if ($sizeIn < $size) {
            // add "\0" if the given bitset is not long enough
            $bitset .= str_repeat("\0", $size - $sizeIn);
        } elseif ($sizeIn > $size) {
            $bitset = substr($bitset, 0, $size);
        }
        
        $this->bitset = $bitset;
        
        $this->rewind();
    }

    /**
     * get a bit at the given ordinal
     * 
     * @param $ordinal int Number of bit to get
     * @return boolean
     */
    private function getBit($ordinal)
    {
        return (ord($this->bitset[(int) ($ordinal / 8)]) & 1 << ($ordinal % 8)) !== 0;
    }

    /**
     * set a bit at the given ordinal
     * 
     * @param $ordinal int
     *            number of bit to manipulate
     * @return void
     */
    private function setBit($ordinal)
    {
        $byte = (int) ($ordinal / 8);
        $this->bitset[$byte] = $this->bitset[$byte] | chr(1 << ($ordinal % 8));
    }

    /**
     * reset a bit at the given ordinal
     * 
     * @param $ordinal int
     *            number of bit to set to false
     * @return void
     */
    private function unsetBit($ordinal)
    {
        $byte = (int) ($ordinal / 8);
        $this->bitset[$byte] = $this->bitset[$byte] & chr(~ (1 << ($ordinal % 8)));
    }
}
