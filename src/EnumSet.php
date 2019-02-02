<?php

namespace MabeEnum;

use Countable;
use Iterator;
use InvalidArgumentException;

/**
 * A set of enumerators of the given enumeration (EnumSet<T>)
 * based on an integer or binary bitset depending of given enumeration size.
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumSet implements Iterator, Countable
{
    /**
     * The classname of the Enumeration
     * @var string
     */
    private $enumeration;

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
     * Integer or binary (little endian) bitset
     * @var int|string
     */
    private $bitset = 0;

    /**#@+
     * Defines private method names to be called depended of how the bitset type was set too.
     * ... Integer or binary bitset.
     * ... *Int or *Bin method
     * 
     * @var string
     */
    private $fnDoRewind            = 'doRewindInt';
    private $fnDoCount             = 'doCountInt';
    private $fnDoGetOrdinals       = 'doGetOrdinalsInt';
    private $fnDoGetBit            = 'doGetBitInt';
    private $fnDoSetBit            = 'doSetBitInt';
    private $fnDoUnsetBit          = 'doUnsetBitInt';
    private $fnDoGetBinaryBitsetLe = 'doGetBinaryBitsetLeInt';
    private $fnDoSetBinaryBitsetLe = 'doSetBinaryBitsetLeInt';
    /**#@-*/

    /**
     * Constructor
     *
     * @param string $enumeration The classname of the enumeration
     * @throws InvalidArgumentException
     */
    public function __construct($enumeration)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                "%s can handle subclasses of '%s' only",
                static::class,
                Enum::class
            ));
        }

        $this->enumeration = $enumeration;
        $this->ordinalMax  = \count($enumeration::getConstants());

        // By default the bitset is initialized as integer bitset
        // in case the enumeraton has more enumerators then integer bits
        // we will switch this into a binary bitset
        if ($this->ordinalMax > \PHP_INT_SIZE * 8) {
            // init binary bitset with zeros
            $this->bitset = \str_repeat("\0", (int)\ceil($this->ordinalMax / 8));

            // switch internal binary bitset functions
            $this->fnDoRewind            = 'doRewindBin';
            $this->fnDoCount             = 'doCountBin';
            $this->fnDoGetOrdinals       = 'doGetOrdinalsBin';
            $this->fnDoGetBit            = 'doGetBitBin';
            $this->fnDoSetBit            = 'doSetBitBin';
            $this->fnDoUnsetBit          = 'doUnsetBitBin';
            $this->fnDoGetBinaryBitsetLe = 'doGetBinaryBitsetLeBin';
            $this->fnDoSetBinaryBitsetLe = 'doSetBinaryBitsetLeBin';
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
     * Attach a new enumerator or overwrite an existing one
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function attach($enumerator)
    {
        $enumeration = $this->enumeration;
        $this->{$this->fnDoSetBit}($enumeration::get($enumerator)->getOrdinal());
    }

    /**
     * Detach the given enumerator
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function detach($enumerator)
    {
        $enumeration = $this->enumeration;
        $this->{$this->fnDoUnsetBit}($enumeration::get($enumerator)->getOrdinal());
    }

    /**
     * Test if the given enumerator was attached
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     */
    public function contains($enumerator)
    {
        $enumeration = $this->enumeration;
        return $this->{$this->fnDoGetBit}($enumeration::get($enumerator)->getOrdinal());
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
            return $enumeration::byOrdinal($this->ordinal);
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
        } while (!$this->{$this->fnDoGetBit}($this->ordinal));
    }

    /**
     * Go to the first valid iterator position.
     * If no valid iterator position was found the iterator position will be 0.
     * @return void
     * @uses doRewindBin()
     * @uses doRewindInt()
     */
    public function rewind()
    {
        $this->{$this->fnDoRewind}();
    }

    /**
     * Go to the first valid iterator position.
     * If no valid iterator position was found the iterator position will be 0.
     *
     * This is the binary bitset implementation.
     *
     * @return void
     * @see rewind()
     * @see doRewindInt()
     */
    private function doRewindBin()
    {
        if (\ltrim($this->bitset, "\0") !== '') {
            $this->ordinal = -1;
            $this->next();
        } else {
            $this->ordinal = 0;
        }
    }

    /**
     * Go to the first valid iterator position.
     * If no valid iterator position was found the iterator position will be 0.
     *
     * This is the binary bitset implementation.
     *
     * @return void
     * @see rewind()
     * @see doRewindBin()
     */
    private function doRewindInt()
    {
        if ($this->bitset) {
            $this->ordinal = -1;
            $this->next();
        } else {
            $this->ordinal = 0;
        }
    }

    /**
     * Test if the iterator is in a valid state
     * @return bool
     */
    public function valid()
    {
        return $this->ordinal !== $this->ordinalMax && $this->{$this->fnDoGetBit}($this->ordinal);
    }

    /* Countable */

    /**
     * Count the number of elements
     *
     * @return int
     * @uses doCountBin()
     * @uses doCountInt()
     */
    public function count()
    {
        return $this->{$this->fnDoCount}();
    }

    /**
     * Count the number of elements.
     *
     * This is the binary bitset implementation.
     *
     * @return int
     * @see count()
     * @see doCountInt()
     */
    private function doCountBin()
    {
        $count   = 0;
        $bitset  = $this->bitset;
        $byteLen = \strlen($bitset);
        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($bitset[$bytePos] === "\0") {
                // fast skip null byte
                continue;
            }

            $ord = \ord($bitset[$bytePos]);
            if ($ord & 0b00000001) ++$count;
            if ($ord & 0b00000010) ++$count;
            if ($ord & 0b00000100) ++$count;
            if ($ord & 0b00001000) ++$count;
            if ($ord & 0b00010000) ++$count;
            if ($ord & 0b00100000) ++$count;
            if ($ord & 0b01000000) ++$count;
            if ($ord & 0b10000000) ++$count;
        }
        return $count;
    }

    /**
     * Count the number of elements.
     *
     * This is the integer bitset implementation.
     *
     * @return int
     * @see count()
     * @see doCountBin()
     */
    private function doCountInt()
    {
        $count  = 0;
        $bitset = $this->bitset;

        // PHP does not support right shift unsigned
        if ($bitset < 0) {
            $count = 1;
            $bitset = $bitset & \PHP_INT_MAX;
        }

        // iterate byte by byte and count set bits
        $phpIntBitSize = \PHP_INT_SIZE * 8;
        for ($bitPos = 0; $bitPos < $phpIntBitSize; $bitPos += 8) {
            $bitChk = 0xff << $bitPos;
            $byte = $bitset & $bitChk;
            if ($byte) {
                $byte = $byte >> $bitPos;
                if ($byte & 0b00000001) ++$count;
                if ($byte & 0b00000010) ++$count;
                if ($byte & 0b00000100) ++$count;
                if ($byte & 0b00001000) ++$count;
                if ($byte & 0b00010000) ++$count;
                if ($byte & 0b00100000) ++$count;
                if ($byte & 0b01000000) ++$count;
                if ($byte & 0b10000000) ++$count;
            }

            if ($bitset <= $bitChk) {
                break;
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
        return $this->enumeration === $other->enumeration
            && $this->bitset === $other->bitset;
    }

    /**
     * Check if this EnumSet is a subset of other
     * @param EnumSet $other
     * @return bool
     */
    public function isSubset(EnumSet $other)
    {
        if ($this->enumeration !== $other->enumeration) {
            return false;
        }

        return ($this->bitset & $other->bitset) === $this->bitset;
    }

    /**
     * Check if this EnumSet is a superset of other
     * @param EnumSet $other
     * @return bool
     */
    public function isSuperset(EnumSet $other)
    {
        if ($this->enumeration !== $other->enumeration) {
            return false;
        }

        return ($this->bitset | $other->bitset) === $this->bitset;
    }

    /**
     * Produce a new set with enumerators from both this and other (this | other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the union
     * @return EnumSet
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function union(EnumSet $other)
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $clone = clone $this;
        $clone->bitset = $this->bitset | $other->bitset;
        return $clone;
    }

    /**
     * Produce a new set with enumerators common to both this and other (this & other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the intersect
     * @return EnumSet
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function intersect(EnumSet $other)
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $clone = clone $this;
        $clone->bitset = $this->bitset & $other->bitset;
        return $clone;
    }

    /**
     * Produce a new set with enumerators in this but not in other (this - other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the diff
     * @return EnumSet
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function diff(EnumSet $other)
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $clone = clone $this;
        $clone->bitset = $this->bitset & ~$other->bitset;
        return $clone;
    }

    /**
     * Produce a new set with enumerators in either this and other but not in both (this ^ other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the symmetric difference
     * @return EnumSet
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function symDiff(EnumSet $other)
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $clone = clone $this;
        $clone->bitset = $this->bitset ^ $other->bitset;
        return $clone;
    }

    /**
     * Get ordinal numbers of the defined enumerators as array
     * @return int[]
     * @uses  doGetOrdinalsBin()
     * @uses  doGetOrdinalsInt()
     */
    public function getOrdinals()
    {
        return $this->{$this->fnDoGetOrdinals}();
    }

    /**
     * Get ordinal numbers of the defined enumerators as array.
     *
     * This is the binary bitset implementation.
     *
     * @return int[]
     * @see getOrdinals()
     * @see goGetOrdinalsInt()
     */
    private function doGetOrdinalsBin()
    {
        $ordinals = [];
        $bitset   = $this->bitset;
        $byteLen  = \strlen($bitset);
        for ($bytePos = 0; $bytePos < $byteLen; ++$bytePos) {
            if ($bitset[$bytePos] === "\0") {
                // fast skip null byte
                continue;
            }

            $ord = \ord($bitset[$bytePos]);
            for ($bitPos = 0; $bitPos < 8; ++$bitPos) {
                if ($ord & (1 << $bitPos)) {
                    $ordinals[] = $bytePos * 8 + $bitPos;
                }
            }
        }
        return $ordinals;
    }

    /**
     * Get ordinal numbers of the defined enumerators as array.
     *
     * This is the integer bitset implementation.
     *
     * @return int[]
     * @see getOrdinals()
     * @see doGetOrdinalsBin()
     */
    private function doGetOrdinalsInt()
    {
        $ordinals   = [];
        $ordinalMax = $this->ordinalMax;
        $bitset     = $this->bitset;
        for ($ord = 0; $ord < $ordinalMax; ++$ord) {
            if ($bitset & (1 << $ord)) {
                $ordinals[] = $ord;
            }
        }
        return $ordinals;
    }

    /**
     * Get values of the defined enumerators as array
     * @return mixed[]
     */
    public function getValues()
    {
        $enumeration = $this->enumeration;
        $values      = [];
        foreach ($this->getOrdinals() as $ord) {
            $values[] = $enumeration::byOrdinal($ord)->getValue();
        }
        return $values;
    }

    /**
     * Get names of the defined enumerators as array
     * @return string[]
     */
    public function getNames()
    {
        $enumeration = $this->enumeration;
        $names       = [];
        foreach ($this->getOrdinals() as $ord) {
            $names[] = $enumeration::byOrdinal($ord)->getName();
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
        $enumerators = [];
        foreach ($this->getOrdinals() as $ord) {
            $enumerators[] = $enumeration::byOrdinal($ord);
        }
        return $enumerators;
    }

    /**
     * Get binary bitset in little-endian order
     * 
     * @return string
     * @uses doGetBinaryBitsetLeBin()
     * @uses doGetBinaryBitsetLeInt()
     */
    public function getBinaryBitsetLe()
    {
        return $this->{$this->fnDoGetBinaryBitsetLe}();
    }

    /**
     * Get binary bitset in little-endian order.
     *
     * This is the binary bitset implementation.
     *
     * @return string
     * @see getBinaryBitsetLe()
     * @see doGetBinaryBitsetLeInt()
     */
    private function doGetBinaryBitsetLeBin()
    {
        return $this->bitset;
    }

    /**
     * Get binary bitset in little-endian order.
     *
     * This is the integer bitset implementation.
     *
     * @return string
     * @see getBinaryBitsetLe()
     * @see doGetBinaryBitsetLeBin()
     */
    private function doGetBinaryBitsetLeInt()
    {
        $bin = \pack(\PHP_INT_SIZE === 8 ? 'P' : 'V', $this->bitset);
        return \substr($bin, 0, (int)\ceil($this->ordinalMax / 8));
    }

    /**
     * Set binary bitset in little-endian order
     *
     * NOTE: It resets the current position of the iterator
     * 
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On a non string is given as Parameter
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @uses doSetBinaryBitsetLeBin()
     * @uses doSetBinaryBitsetLeInt()
     */
    public function setBinaryBitsetLe($bitset)
    {
        if (!\is_string($bitset)) {
            throw new InvalidArgumentException('Bitset must be a string');
        }

        $this->{$this->fnDoSetBinaryBitsetLe}($bitset);

        // reset the iterator position
        $this->rewind();
    }

    /**
     * Set binary bitset in little-endian order
     *
     * NOTE: It resets the current position of the iterator
     *
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @see setBinaryBitsetLeBin()
     * @see doSetBinaryBitsetLeInt()
     */
    private function doSetBinaryBitsetLeBin($bitset)
    {
        $size   = \strlen($this->bitset);
        $sizeIn = \strlen($bitset);

        if ($sizeIn < $size) {
            // add "\0" if the given bitset is not long enough
            $bitset .= \str_repeat("\0", $size - $sizeIn);
        } elseif ($sizeIn > $size) {
            if (\ltrim(\substr($bitset, $size), "\0") !== '') {
                throw new InvalidArgumentException('out-of-range bits detected');
            }
            $bitset = \substr($bitset, 0, $size);
        }

        // truncate out-of-range bits of last byte
        $lastByteMaxOrd = $this->ordinalMax % 8;
        if ($lastByteMaxOrd !== 0) {
            $lastByte         = $bitset[$size - 1];
            $lastByteExpected = \chr((1 << $lastByteMaxOrd) - 1) & $lastByte;
            if ($lastByte !== $lastByteExpected) {
                throw new InvalidArgumentException('out-of-range bits detected');
            }

            $this->bitset = \substr($bitset, 0, -1) . $lastByteExpected;
        }

        $this->bitset = $bitset;
    }

    /**
     * Set binary bitset in little-endian order
     *
     * NOTE: It resets the current position of the iterator
     *
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @see setBinaryBitsetLeBin()
     * @see doSetBinaryBitsetLeBin()
     */
    private function doSetBinaryBitsetLeInt($bitset)
    {
        $len = \strlen($bitset);
        $int = 0;
        for ($i = 0; $i < $len; ++$i) {
            $ord = \ord($bitset[$i]);

            if ($ord && $i > \PHP_INT_SIZE - 1) {
                throw new InvalidArgumentException('out-of-range bits detected');
            }

            $int |= $ord << (8 * $i);
        }

        if ($int & (~0 << $this->ordinalMax)) {
            throw new InvalidArgumentException('out-of-range bits detected');
        }

        $this->bitset = $int;
    }

    /**
     * Get binary bitset in big-endian order
     * 
     * @return string
     */
    public function getBinaryBitsetBe()
    {
        return \strrev($this->bitset);
    }

    /**
     * Set binary bitset in big-endian order
     *
     * NOTE: It resets the current position of the iterator
     * 
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On a non string is given as Parameter
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     */
    public function setBinaryBitsetBe($bitset)
    {
        if (!\is_string($bitset)) {
            throw new InvalidArgumentException('Bitset must be a string');
        }
        $this->setBinaryBitsetLe(\strrev($bitset));
    }

    /**
     * Get a bit at the given ordinal number
     *
     * @param int $ordinal Ordinal number of bit to get
     * @return bool
     * @throws InvalidArgumentException If the given ordinal number is out-of-range
     * @uses doGetBitBin()
     * @uses doGetBitInt()
     */
    public function getBit($ordinal)
    {
        if ($ordinal < 0 || $ordinal > $this->ordinalMax) {
            throw new InvalidArgumentException("Ordinal number must be between 0 and {$this->ordinalMax}");
        }

        return $this->{$this->fnDoGetBit}($ordinal);
    }

    /**
     * Get a bit at the given ordinal number.
     *
     * This is the binary bitset implementation.
     *
     * @param int $ordinal Ordinal number of bit to get
     * @return bool
     * @see getBit()
     * @see doGetBitInt()
     */
    private function doGetBitBin($ordinal)
    {
        return (\ord($this->bitset[(int) ($ordinal / 8)]) & 1 << ($ordinal % 8)) !== 0;
    }

    /**
     * Get a bit at the given ordinal number.
     *
     * This is the integer bitset implementation.
     * 
     * @param int $ordinal Ordinal number of bit to get
     * @return bool
     * @see getBit()
     * @see doGetBitBin()
     */
    private function doGetBitInt($ordinal)
    {
        return (bool)($this->bitset & (1 << $ordinal));
    }

    /**
     * Set a bit at the given ordinal number
     *
     * @param int $ordinal Ordinal number of bit to set
     * @param bool $bit    The bit to set
     * @return void
     * @throws InvalidArgumentException If the given ordinal number is out-of-range
     * @uses doSetBitBin()
     * @uses doSetBitInt()
     * @uses doUnsetBitBin()
     * @uses doUnsetBitInt()
     */
    public function setBit($ordinal, $bit)
    {
        if ($ordinal < 0 || $ordinal > $this->ordinalMax) {
            throw new InvalidArgumentException("Ordinal number must be between 0 and {$this->ordinalMax}");
        }

        if ($bit) {
            $this->{$this->fnDoSetBit}($ordinal);
        } else {
            $this->{$this->fnDoUnsetBit}($ordinal);
        }
    }

    /**
     * Set a bit at the given ordinal number.
     *
     * This is the binary bitset implementation.
     * 
     * @param int $ordinal Ordinal number of bit to set
     * @return void
     * @see setBit()
     * @see doSetBitInt()
     */
    private function doSetBitBin($ordinal)
    {
        $byte = (int) ($ordinal / 8);
        $this->bitset[$byte] = $this->bitset[$byte] | \chr(1 << ($ordinal % 8));
    }

    /**
     * Set a bit at the given ordinal number.
     *
     * This is the binary bitset implementation.
     *
     * @param int $ordinal Ordinal number of bit to set
     * @return void
     * @see setBit()
     * @see doSetBitBin()
     */
    private function doSetBitInt($ordinal)
    {
        $this->bitset = $this->bitset | (1 << $ordinal);
    }

    /**
     * Unset a bit at the given ordinal number.
     *
     * This is the binary bitset implementation.
     *
     * @param int $ordinal Ordinal number of bit to unset
     * @return void
     * @see setBit()
     * @see doUnsetBitInt()
     */
    private function doUnsetBitBin($ordinal)
    {
        $byte = (int) ($ordinal / 8);
        $this->bitset[$byte] = $this->bitset[$byte] & \chr(~(1 << ($ordinal % 8)));
    }

    /**
     * Unset a bit at the given ordinal number.
     *
     * This is the integer bitset implementation.
     *
     * @param int $ordinal Ordinal number of bit to unset
     * @return void
     * @see setBit()
     * @see doUnsetBitBin()
     */
    private function doUnsetBitInt($ordinal)
    {
        $this->bitset = $this->bitset & ~(1 << $ordinal);
    }
}
