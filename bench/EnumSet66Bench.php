<?php

namespace MabeEnumBench;

use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\Enum66;

/**
 * Benchmark of EnumSet used with an enumeration of 66 enumerators.
 * (The internal bitset have to be a binary string)
 *
 * @BeforeMethods({"init"})
 * @Revs(2000)
 * @Iterations(25)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSet66Bench
{
    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @var Enum66[]
     */
    private $enumerators;

    /**
     * @var EnumSet
     */
    private $emptySet;

    /**
     * @var EnumSet
     */
    private $fullSet;

    /**
     * Will be called before every subject
     */
    public function init()
    {
        $this->values      = Enum66::getValues();
        $this->enumerators = Enum66::getEnumerators();

        $this->emptySet = new EnumSet(Enum66::class);
        $this->fullSet  = new EnumSet(Enum66::class);
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->attach($enumerator);
        }
    }

    public function benchAttachEnumeratorOnEmpty()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->attach($enumerator);
        }
    }

    public function benchAttachValueOnEmpty()
    {
        foreach ($this->values as $value) {
            $this->emptySet->attach($value);
        }
    }

    public function benchAttachEnumeratorOnFull()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->attach($enumerator);
        }
    }

    public function benchAttachValueOnFull()
    {
        foreach ($this->values as $value) {
            $this->fullSet->attach($value);
        }
    }

    public function benchDetachEnumeratorOnEmpty()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->detach($enumerator);
        }
    }

    public function benchDetachValueOnEmpty()
    {
        foreach ($this->values as $value) {
            $this->emptySet->detach($value);
        }
    }

    public function benchDetachEnumeratorOnFull()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->detach($enumerator);
        }
    }

    public function benchDetachValueOnFull()
    {
        foreach ($this->values as $value) {
            $this->fullSet->detach($value);
        }
    }

    public function benchContainsEnumeratorTrue()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->contains($enumerator);
        }
    }

    public function benchContainsValueTrue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->contains($value);
        }
    }

    public function benchContainsEnumeratorFalse()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->contains($enumerator);
        }
    }

    public function benchContainsValueFalse()
    {
        foreach ($this->values as $value) {
            $this->fullSet->contains($value);
        }
    }

    public function benchIterateFull()
    {
        foreach ($this->fullSet as $enumerator) {
            $enumerator->getValue();
        }
    }

    public function benchIterateEmpty()
    {
        foreach ($this->emptySet as $enumerator) {
            $enumerator->getValue();
        }
    }

    public function benchCountFull()
    {
        $this->fullSet->count();
    }

    public function benchCountEmpty()
    {
        $this->emptySet->count();
    }

    public function benchIsEqual()
    {
        $this->fullSet->isEqual($this->fullSet);
    }

    public function benchIsSubset()
    {
        $this->fullSet->isEqual($this->fullSet);
    }

    public function benchIsSuperset()
    {
        $this->fullSet->isSuperset($this->fullSet);
    }

    public function benchUnion()
    {
        $this->fullSet->union($this->emptySet);
    }

    public function benchIntersect()
    {
        $this->fullSet->intersect($this->emptySet);
    }

    public function benchDiff()
    {
        $this->fullSet->diff($this->emptySet);
    }

    public function benchSymDiff()
    {
        $this->fullSet->symDiff($this->emptySet);
    }

    public function benchGetOrdinalsFull()
    {
        $this->fullSet->getOrdinals();
    }

    public function benchGetOrdinalsEmpty()
    {
        $this->emptySet->getOrdinals();
    }

    public function benchGetValues()
    {
        $this->fullSet->getValues();
    }

    public function benchGetNames()
    {
        $this->fullSet->getNames();
    }

    public function benchGetEnumerators()
    {
        $this->fullSet->getEnumerators();
    }
}
