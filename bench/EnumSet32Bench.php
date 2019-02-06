<?php

namespace MabeEnumBench;

use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\Enum32;

/**
 * Benchmark of EnumSet used with an enumeration of 32 enumerators.
 * (The internal bitset could be both an integer and a binary string)
 *
 * @BeforeMethods({"init"})
 * @Revs(2000)
 * @Iterations(25)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSet32Bench
{
    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @var Enum32[]
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
        $this->values      = Enum32::getValues();
        $this->enumerators = Enum32::getEnumerators();

        $this->emptySet = new EnumSet(Enum32::class);
        $this->fullSet  = new EnumSet(Enum32::class, $this->enumerators);
    }

    public function benchAttachEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->attachEnumerator($enumerator);
        }
    }

    public function benchWithEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->withEnumerator($enumerator);
        }
    }

    public function benchAttachEnumerators()
    {
        $this->emptySet->withEnumerators($this->enumerators);
    }

    public function benchWithEnumerators()
    {
        $this->emptySet->attachEnumerators($this->enumerators);
    }

    public function benchWithValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->withEnumerator($value);
        }
    }

    public function benchAttachValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->attachEnumerator($value);
        }
    }

    public function benchAttachValues()
    {
        $this->emptySet->attachEnumerators($this->values);
    }

    public function benchWithValues()
    {
        $this->emptySet->withEnumerators($this->values);
    }

    public function benchDetachEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->detachEnumerator($enumerator);
        }
    }

    public function benchWithoutEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->withoutEnumerator($enumerator);
        }
    }

    public function benchDetachEnumerators()
    {
        $this->fullSet->detachEnumerators($this->enumerators);
    }

    public function benchWithoutEnumerators()
    {
        $this->fullSet->withoutEnumerators($this->enumerators);
    }

    public function benchDetachValue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->detachEnumerator($value);
        }
    }

    public function benchWithoutValue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->withoutEnumerator($value);
        }
    }

    public function benchDetachValues()
    {
        $this->fullSet->detachEnumerators($this->values);
    }

    public function benchWithoutValues()
    {
        $this->fullSet->withoutEnumerators($this->values);
    }

    public function benchContainsEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->contains($enumerator);
        }
    }

    public function benchContainsValue()
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

    public function benchSetUnion()
    {
        $this->fullSet->setUnion($this->emptySet);
    }

    public function benchWithUnion()
    {
        $this->fullSet->withUnion($this->emptySet);
    }

    public function benchSetIntersect()
    {
        $this->fullSet->setIntersect($this->emptySet);
    }

    public function benchWithIntersect()
    {
        $this->fullSet->withIntersect($this->emptySet);
    }

    public function benchSetDiff()
    {
        $this->fullSet->setDiff($this->emptySet);
    }

    public function benchWithDiff()
    {
        $this->fullSet->withDiff($this->emptySet);
    }

    public function benchSetSymDiff()
    {
        $this->fullSet->setSymDiff($this->emptySet);
    }

    public function benchWithSymDiff()
    {
        $this->fullSet->withSymDiff($this->emptySet);
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
