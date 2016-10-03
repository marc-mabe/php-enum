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
            if (++$this->ordinal >= $this->ordinalMax) {
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
        $count   = 0;
        $byteLen = strlen($this->bitset);
        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($this->bitset[$bytePos] === "\0") {
                continue; // fast skip null byte
            }

            for ($bitPos = 0; $bitPos < 8; ++$bitPos) {
                if ((ord($this->bitset[$bytePos]) & (1 << $bitPos)) !== 0) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    /**
     * Check if this EnumSet is the same as other
     * @param EnumSet $other
     * @return bool
     */
    public function isEqual(EnumSet $other)
    {
        return $this->getEnumeration() === $other->getEnumeration()
            && $this->getBinaryBitsetLe() === $other->getBinaryBitsetLe();
    }

    /**
     * Check if this EnumSet is a subset of other
     * @param EnumSet $other
     * @return bool
     */
    public function isSubset(EnumSet $other)
    {
        if ($this->getEnumeration() !== $other->getEnumeration()) {
            return false;
        }

        $thisBitset = $this->getBinaryBitsetLe();
        return ($thisBitset & $other->getBinaryBitsetLe()) === $thisBitset;
    }

    /**
     * Check if this EnumSet is a superset of other
     * @param EnumSet $other
     * @return bool
     */
    public function isSuperset(EnumSet $other)
    {
        if ($this->getEnumeration() !== $other->getEnumeration()) {
            return false;
        }

        $thisBitset = $this->getBinaryBitsetLe();
        return ($thisBitset | $other->getBinaryBitsetLe()) === $thisBitset;
    }

    /**
     * Produce a new set with enumerators from both this and other (this | other)
     * @param EnumSet ...$other Other EnumSet(s) of the same enumeration to produce the union
     * @return EnumSet
     */
    public function union(EnumSet $other)
    {
        $bitset = $this->bitset;
        foreach (func_get_args() as $other) {
            if (!$other instanceof self || $this->enumeration !== $other->enumeration) {
                throw new InvalidArgumentException(sprintf(
                    "Others should be an instance of %s of the same enumeration as this %s",
                    __CLASS__,
                    $this->enumeration
                ));
            }

            $bitset |= $other->bitset;
        }

        $clone = clone $this;
        $clone->bitset = $bitset;
        return $clone;
    }

    /**
     * Produce a new set with enumerators common to both this and other (this & other)
     * @param EnumSet ...$other Other EnumSet(s) of the same enumeration to produce the union
     * @return EnumSet
     */
    public function intersect(EnumSet $other)
    {
        $bitset = $this->bitset;
        foreach (func_get_args() as $other) {
            if (!$other instanceof self || $this->enumeration !== $other->enumeration) {
                throw new InvalidArgumentException(sprintf(
                    "Others should be an instance of %s of the same enumeration as this %s",
                    __CLASS__,
                    $this->enumeration
                ));
            }

            $bitset &= $other->bitset;
        }

        $clone = clone $this;
        $clone->bitset = $bitset;
        return $clone;
    }

    /**
     * Produce a new set with enumerators in this but not in other (this - other)
     * @param EnumSet ...$other Other EnumSet(s) of the same enumeration to produce the union
     * @return EnumSet
     */
    public function diff(EnumSet $other)
    {
        $bitset = '';
        foreach (func_get_args() as $other) {
            if (!$other instanceof self || $this->enumeration !== $other->enumeration) {
                throw new InvalidArgumentException(sprintf(
                    "Others should be an instance of %s of the same enumeration as this %s",
                    __CLASS__,
                    $this->enumeration
                ));
            }

            $bitset |= $other->bitset;
        }

        $clone = clone $this;
        $clone->bitset = $this->bitset & ~$bitset;
        return $clone;
    }

    /**
     * Produce a new set with enumerators in either this and other but not in both (this ^ (other | other))
     * @param EnumSet ...$other Other EnumSet(s) of the same enumeration to produce the union
     * @return EnumSet
     */
    public function symDiff(EnumSet $other)
    {
        $bitset = '';
        foreach (func_get_args() as $other) {
            if (!$other instanceof self || $this->enumeration !== $other->enumeration) {
                throw new InvalidArgumentException(sprintf(
                    "Others should be an instance of %s of the same enumeration as this %s",
                    __CLASS__,
                    $this->enumeration
                ));
            }

            $bitset |= $other->bitset;
        }

        $clone = clone $this;
        $clone->bitset = $this->bitset ^ $bitset;
        return $clone;
    }

    /**
     * Get ordinal numbers of the defined enumerators as array
     * @return int[]
     */
    public function getOrdinals()
    {
        $ordinals = array();
        $byteLen  = strlen($this->bitset);

        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($this->bitset[$bytePos] === "\0") {
                continue; // fast skip null byte
            }

            for ($bitPos = 0; $bitPos < 8; ++$bitPos) {
                if ((ord($this->bitset[$bytePos]) & (1 << $bitPos)) !== 0) {
                    $ordinals[] = $bytePos * 8 + $bitPos;
                }
            }
        }

        return $ordinals;
    }

    /**
     * Get values of the defined enumerators as array
     * @return null[]|bool[]|int[]|float[]|string[]
     */
    public function getValues()
    {
        $values = array();
        foreach ($this->getEnumerators() as $enumerator) {
            $values[] = $enumerator->getValue();
        }
        return $values;
    }

    /**
     * Get names of the defined enumerators as array
     * @return string[]
     */
    public function getNames()
    {
        $names = array();
        foreach ($this->getEnumerators() as $enumerator) {
            $names[] = $enumerator->getName();
        }
        return $names;
    }

    /**
     * Get the defined enumerators as array
     * @return Enum[]
     */
    public function getEnumerators()
    {
        $enumeration = $this->enumeration;
        $enumerators = array();
        foreach ($this->getOrdinals() as $ord) {
            $enumerators[] = $enumeration::getByOrdinal($ord);
        }
        return $enumerators;
    }

    /**
     * Get binary bitset in little-endian order
     * 
     * @return string
     */
    public function getBinaryBitsetLe()
    {
        return $this->bitset;
    }

    /**
     * Set binary bitset in little-endian order
     *
     * NOTE: It resets the current position of the iterator
     * 
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On a non string is given as Parameter
     */
    public function setBinaryBitsetLe($bitset)
    {
        if (!is_string($bitset)) {
            throw new InvalidArgumentException('Bitset must be a string');
        }

        $size   = strlen($this->bitset);
        $sizeIn = strlen($bitset);

        if ($sizeIn < $size) {
            // add "\0" if the given bitset is not long enough
            $bitset .= str_repeat("\0", $size - $sizeIn);
        } elseif ($sizeIn > $size) {
            $bitset = substr($bitset, 0, $size);
        }

        // truncate out-of-range bits of last byte
        $lastByteMaxOrd = $this->ordinalMax % 8;
        if ($lastByteMaxOrd === 0) {
            $this->bitset = $bitset;
        } else {
            $lastByte     = chr($lastByteMaxOrd) & $bitset[$size - 1];
            $this->bitset = substr($bitset, 0, -1) . $lastByte;
        }

        // reset the iterator position
        $this->rewind();
    }

    /**
     * Get binary bitset in big-endian order
     * 
     * @return string
     */
    public function getBinaryBitsetBe()
    {
        return strrev($this->bitset);
    }

    /**
     * Set binary bitset in big-endian order
     *
     * NOTE: It resets the current position of the iterator
     * 
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On a non string is given as Parameter
     */
    public function setBinaryBitsetBe($bitset)
    {
        if (!is_string($bitset)) {
            throw new InvalidArgumentException('Bitset must be a string');
        }
        
	$this->setBinaryBitsetLe(strrev($bitset));
    }

    /**
     * Get the binary bitset
     * 
     * @return string Returns the binary bitset in big-endian order
     * @deprecated Please use getBinaryBitsetBe() instead
     */
    public function getBitset()
    {
        return $this->getBinaryBitsetBe();
    }

    /**
     * Set the bitset.
     * NOTE: It resets the current position of the iterator
     * 
     * @param string $bitset The binary bitset in big-endian order
     * @return void
     * @throws InvalidArgumentException On a non string is given as Parameter
     * @deprecated Please use setBinaryBitsetBe() instead
     */
    public function setBitset($bitset)
    {
        $this->setBinaryBitsetBE($bitset);
    }

    /**
     * Get a bit at the given ordinal number
     * 
     * @param $ordinal int Ordinal number of bit to get
     * @return boolean
     */
    private function getBit($ordinal)
    {
        return (ord($this->bitset[(int) ($ordinal / 8)]) & 1 << ($ordinal % 8)) !== 0;
    }

    /**
     * Set a bit at the given ordinal number
     * 
     * @param $ordinal int Ordnal number of bit to set
     * @return void
     */
    private function setBit($ordinal)
    {
        $byte = (int) ($ordinal / 8);
        $this->bitset[$byte] = $this->bitset[$byte] | chr(1 << ($ordinal % 8));
    }

    /**
     * Unset a bit at the given ordinal number
     * 
     * @param $ordinal int Ordinal number of bit to unset
     * @return void
     */
    private function unsetBit($ordinal)
    {
        $byte = (int) ($ordinal / 8);
        $this->bitset[$byte] = $this->bitset[$byte] & chr(~(1 << ($ordinal % 8)));
    }
}
