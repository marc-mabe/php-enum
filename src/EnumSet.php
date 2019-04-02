<?php

declare(strict_types=1);

namespace MabeEnum;

use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;

/**
 * A set of enumerators of the given enumeration (EnumSet<T extends Enum>)
 * based on an integer or binary bitset depending of given enumeration size.
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumSet implements IteratorAggregate, Countable
{
    /**
     * The classname of the Enumeration
     * @var string
     */
    private $enumeration;

    /**
     * Number of enumerators defined in the enumeration
     * @var int
     */
    private $enumerationCount;

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
    private $fnDoGetIterator       = 'doGetIteratorInt';
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
     * @param iterable|null $enumerators iterable list of enumerators initializing the set
     * @throws InvalidArgumentException
     */
    public function __construct(string $enumeration, iterable $enumerators = null)
    {
        if (!\is_subclass_of($enumeration, Enum::class)) {
            throw new InvalidArgumentException(\sprintf(
                '%s can handle subclasses of %s only',
                __METHOD__,
                Enum::class
            ));
        }

        $this->enumeration      = $enumeration;
        $this->enumerationCount = \count($enumeration::getConstants());

        // By default the bitset is initialized as integer bitset
        // in case the enumeraton has more enumerators then integer bits
        // we will switch this into a binary bitset
        if ($this->enumerationCount > \PHP_INT_SIZE * 8) {
            // init binary bitset with zeros
            $this->bitset = \str_repeat("\0", (int)\ceil($this->enumerationCount / 8));

            // switch internal binary bitset functions
            $this->fnDoGetIterator       = 'doGetIteratorBin';
            $this->fnDoCount             = 'doCountBin';
            $this->fnDoGetOrdinals       = 'doGetOrdinalsBin';
            $this->fnDoGetBit            = 'doGetBitBin';
            $this->fnDoSetBit            = 'doSetBitBin';
            $this->fnDoUnsetBit          = 'doUnsetBitBin';
            $this->fnDoGetBinaryBitsetLe = 'doGetBinaryBitsetLeBin';
            $this->fnDoSetBinaryBitsetLe = 'doSetBinaryBitsetLeBin';
        }

        if ($enumerators !== null) {
            foreach ($enumerators as $enumerator) {
                $this->{$this->fnDoSetBit}($enumeration::get($enumerator)->getOrdinal());
            }
        }
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
     * Attach an enumerator object or value
     * @param Enum|null|bool|int|float|string|array $enumerator Enumerator object or value
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function attachEnumerator($enumerator): void
    {
        $this->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
    }

    /**
     * Creates a new set with the given enumerator object or value attached
     * @param Enum|null|bool|int|float|string|array $enumerator Enumerator object or value
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function withEnumerator($enumerator): self
    {
        $clone = clone $this;
        $clone->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        return $clone;
    }

    /**
     * Attach all enumerator objects or values of the given iterable
     * @param iterable $enumerators Iterable list of enumerator objects or values
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function attachEnumerators(iterable $enumerators): void
    {
        foreach ($enumerators as $enumerator) {
            $this->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        }
    }

    /**
     * Creates a new set with the given enumeration objects or values attached
     * @param iterable $enumerators Iterable list of enumerator objects or values
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function withEnumerators(iterable $enumerators): self
    {
        $clone = clone $this;
        foreach ($enumerators as $enumerator) {
            $clone->{$this->fnDoSetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        }
        return $clone;
    }

    /**
     * Detach the given enumerator object or value
     * @param Enum|null|bool|int|float|string|array $enumerator Enumerator object or value
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function detachEnumerator($enumerator): void
    {
        $this->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
    }

    /**
     * Create a new set with the given enumerator object or value detached
     * @param Enum|null|bool|int|float|string|array $enumerator Enumerator object or value
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function withoutEnumerator($enumerator): self
    {
        $clone = clone $this;
        $clone->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        return $clone;
    }

    /**
     * Detach all enumerator objects or values of the given iterable
     * @param iterable $enumerators Iterable list of enumerator objects or values
     * @return void
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function detachEnumerators(iterable $enumerators): void
    {
        foreach ($enumerators as $enumerator) {
            $this->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        }
    }

    /**
     * Creates a new set with the given enumeration objects or values detached
     * @param iterable $enumerators Iterable list of enumerator objects or values
     * @return static
     * @throws InvalidArgumentException On an invalid given enumerator
     */
    public function withoutEnumerators(iterable $enumerators): self
    {
        $clone = clone $this;
        foreach ($enumerators as $enumerator) {
            $clone->{$this->fnDoUnsetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
        }
        return $clone;
    }

    /**
     * Test if the given enumerator exists
     * @param Enum|null|bool|int|float|string|array $enumerator
     * @return bool
     */
    public function contains($enumerator): bool
    {
        return $this->{$this->fnDoGetBit}(($this->enumeration)::get($enumerator)->getOrdinal());
    }

    /* IteratorAggregate */

    /**
     * Get a new iterator
     * @return Iterator
     * @uses doGetIteratorInt()
     * @uses doGetIteratorBin()
     */
    public function getIterator(): Iterator
    {
        return $this->{$this->fnDoGetIterator}();
    }

    /**
     * Get a new Iterator.
     *
     * This is the binary bitset implementation.
     *
     * @return Iterator
     * @see getIterator()
     * @see goGetIteratorInt()
     */
    private function doGetIteratorBin()
    {
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
                    $ordinal = $bytePos * 8 + $bitPos;
                    yield $ordinal => ($this->enumeration)::byOrdinal($ordinal);
                }
            }
        }
    }

    /**
     * Get a new Iterator.
     *
     * This is the integer bitset implementation.
     *
     * @return Iterator
     * @see getIterator()
     * @see doGetIteratorBin()
     */
    private function doGetIteratorInt()
    {
        $count  = $this->enumerationCount;
        $bitset = $this->bitset;
        for ($ordinal = 0; $ordinal < $count; ++$ordinal) {
            if ($bitset & (1 << $ordinal)) {
                yield $ordinal => ($this->enumeration)::byOrdinal($ordinal);
            }
        }
    }

    /* Countable */

    /**
     * Count the number of elements
     *
     * @return int
     * @uses doCountBin()
     * @uses doCountInt()
     */
    public function count(): int
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
            $count  = 1;
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
    public function isEqual(EnumSet $other): bool
    {
        return $this->enumeration === $other->enumeration
            && $this->bitset === $other->bitset;
    }

    /**
     * Check if this EnumSet is a subset of other
     * @param EnumSet $other
     * @return bool
     */
    public function isSubset(EnumSet $other): bool
    {
        return $this->enumeration === $other->enumeration
            && ($this->bitset & $other->bitset) === $this->bitset;
    }

    /**
     * Check if this EnumSet is a superset of other
     * @param EnumSet $other
     * @return bool
     */
    public function isSuperset(EnumSet $other): bool
    {
        return $this->enumeration === $other->enumeration
            && ($this->bitset | $other->bitset) === $this->bitset;
    }

    /**
     * Modify this set from both this and other (this | other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the union
     * @return void
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function setUnion(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset | $other->bitset;
    }

    /**
     * Create a new set with enumerators from both this and other (this | other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the union
     * @return static
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function withUnion(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setUnion($other);
        return $clone;
    }

    /**
     * Modify this set with enumerators common to both this and other (this & other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the intersect
     * @return void
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function setIntersect(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset & $other->bitset;
    }

    /**
     * Create a new set with enumerators common to both this and other (this & other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the intersect
     * @return static
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function withIntersect(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setIntersect($other);
        return $clone;
    }

    /**
     * Modify this set with enumerators in this but not in other (this - other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the diff
     * @return void
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function setDiff(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset & ~$other->bitset;
    }

    /**
     * Modify this set with enumerators in this but not in other (this - other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the diff
     * @return static
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function withDiff(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setDiff($other);
        return $clone;
    }

    /**
     * Modify this set with enumerators in either this and other but not in both (this ^ other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the symmetric difference
     * @return void
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function setSymDiff(EnumSet $other): void
    {
        if ($this->enumeration !== $other->enumeration) {
            throw new InvalidArgumentException(\sprintf(
                'Other should be of the same enumeration as this %s',
                $this->enumeration
            ));
        }

        $this->bitset = $this->bitset ^ $other->bitset;
    }

    /**
     * Create a new set with enumerators in either this and other but not in both (this ^ other)
     *
     * @param EnumSet $other EnumSet of the same enumeration to produce the symmetric difference
     * @return static
     * @throws InvalidArgumentException If $other doesn't match the enumeration
     */
    public function withSymDiff(EnumSet $other): self
    {
        $clone = clone $this;
        $clone->setSymDiff($other);
        return $clone;
    }

    /**
     * Get ordinal numbers of the defined enumerators as array
     * @return int[]
     * @uses  doGetOrdinalsBin()
     * @uses  doGetOrdinalsInt()
     */
    public function getOrdinals(): array
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
        $ordinals = [];
        $count    = $this->enumerationCount;
        $bitset   = $this->bitset;
        for ($ordinal = 0; $ordinal < $count; ++$ordinal) {
            if ($bitset & (1 << $ordinal)) {
                $ordinals[] = $ordinal;
            }
        }
        return $ordinals;
    }

    /**
     * Get values of the defined enumerators as array
     * @return mixed[]
     */
    public function getValues(): array
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
    public function getNames(): array
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
    public function getEnumerators(): array
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
    public function getBinaryBitsetLe(): string
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
        return \substr($bin, 0, (int)\ceil($this->enumerationCount / 8));
    }

    /**
     * Set the given binary bitset in little-endian order
     *
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @uses doSetBinaryBitsetLeBin()
     * @uses doSetBinaryBitsetLeInt()
     */
    public function setBinaryBitsetLe(string $bitset): void
    {
        $this->{$this->fnDoSetBinaryBitsetLe}($bitset);
    }

    /**
     * Create a new set with the given binary bitset in little-endian order
     *
     * @param string $bitset
     * @return static
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @uses doSetBinaryBitsetLeBin()
     * @uses doSetBinaryBitsetLeInt()
     */
    public function withBinaryBitsetLe(string $bitset): self
    {
        $clone = clone $this;
        $clone->{$this->fnDoSetBinaryBitsetLe}($bitset);
        return $clone;
    }

    /**
     * Set binary bitset in little-endian order
     *
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @see setBinaryBitsetLeBin()
     * @see doSetBinaryBitsetLeInt()
     */
    private function doSetBinaryBitsetLeBin($bitset): void
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
        $lastByteMaxOrd = $this->enumerationCount % 8;
        if ($lastByteMaxOrd !== 0) {
            $lastByte         = $bitset[-1];
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
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     * @see setBinaryBitsetLeBin()
     * @see doSetBinaryBitsetLeBin()
     */
    private function doSetBinaryBitsetLeInt($bitset): void
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

        if ($int & (~0 << $this->enumerationCount)) {
            throw new InvalidArgumentException('out-of-range bits detected');
        }

        $this->bitset = $int;
    }

    /**
     * Get binary bitset in big-endian order
     * 
     * @return string
     */
    public function getBinaryBitsetBe(): string
    {
        return \strrev($this->bitset);
    }

    /**
     * Set the given binary bitset in big-endian order
     *
     * @param string $bitset
     * @return void
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     */
    public function setBinaryBitsetBe(string $bitset): void
    {
        $this->{$this->fnDoSetBinaryBitsetLe}(\strrev($bitset));
    }

    /**
     * Create a new set with the given binary bitset in big-endian order
     *
     * @param string $bitset
     * @return static
     * @throws InvalidArgumentException On out-of-range bits given as input bitset
     */
    public function withBinaryBitsetBe(string $bitset): self
    {
        $clone = $this;
        $clone->{$this->fnDoSetBinaryBitsetLe}(\strrev($bitset));
        return $clone;
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
    public function getBit(int $ordinal): bool
    {
        if ($ordinal < 0 || $ordinal > $this->enumerationCount) {
            throw new InvalidArgumentException("Ordinal number must be between 0 and {$this->enumerationCount}");
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
    public function setBit(int $ordinal, bool $bit): void
    {
        if ($ordinal < 0 || $ordinal > $this->enumerationCount) {
            throw new InvalidArgumentException("Ordinal number must be between 0 and {$this->enumerationCount}");
        }

        if ($bit) {
            $this->{$this->fnDoSetBit}($ordinal);
        } else {
            $this->{$this->fnDoUnsetBit}($ordinal);
        }
    }

    /**
     * Create a new set with the bit at the given ordinal number set
     *
     * @param int $ordinal Ordinal number of bit to set
     * @param bool $bit    The bit to set
     * @return static
     * @throws InvalidArgumentException If the given ordinal number is out-of-range
     * @uses doSetBitBin()
     * @uses doSetBitInt()
     * @uses doUnsetBitBin()
     * @uses doUnsetBitInt()
     */
    public function withBit(int $ordinal, bool $bit): self
    {
        $clone = clone $this;
        $clone->setBit($ordinal, $bit);
        return $clone;
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
    private function doSetBitBin($ordinal): void
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
    private function doSetBitInt($ordinal): void
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
    private function doUnsetBitBin($ordinal): void
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
    private function doUnsetBitInt($ordinal): void
    {
        $this->bitset = $this->bitset & ~(1 << $ordinal);
    }
}
