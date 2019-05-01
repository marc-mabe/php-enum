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
        $this->fullSet  = new EnumSet(Enum66::class, $this->enumerators);
    }

    public function benchAddEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->add($enumerator);
        }
    }

    public function benchWithEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->with($enumerator);
        }
    }

    public function benchAddIterableEnumerators()
    {
        $this->emptySet->addIterable($this->enumerators);
    }

    public function benchWithEnumerators()
    {
        $this->emptySet->withIterable($this->enumerators);
    }

    public function benchAttachValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->add($value);
        }
    }

    public function benchWithValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->with($value);
        }
    }

    public function benchAttachValues()
    {
        $this->emptySet->addIterable($this->values);
    }

    public function benchWithValues()
    {
        $this->emptySet->withIterable($this->values);
    }

    public function benchDetachEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->remove($enumerator);
        }
    }

    public function benchWithoutEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->without($enumerator);
        }
    }

    public function benchDetachEnumerators()
    {
        $this->emptySet->removeIterable($this->enumerators);
    }

    public function benchWithoutEnumerators()
    {
        $this->emptySet->withoutIterable($this->enumerators);
    }

    public function benchDetachValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->remove($value);
        }
    }

    public function benchWithoutValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->without($value);
        }
    }

    public function benchDetachValues()
    {
        $this->emptySet->removeIterable($this->values);
    }

    public function benchWithoutValues()
    {
        $this->emptySet->withoutIterable($this->values);
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
