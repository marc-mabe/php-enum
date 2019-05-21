<?php

namespace MabeEnumBench;

use MabeEnum\Enum;
use MabeEnum\EnumSet;

/**
 * An abstract benchmark of EnumSet to be used with differrent enumerations.
 * (The internal bitset could be both an integer and a binary string)
 *
 * @BeforeMethods({"init"})
 * @Revs(2000)
 * @Iterations(25)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
abstract class AbstractEnumSetBench
{
    /**
     * @var mixed[]
     */
    protected $values;

    /**
     * @var Enum[]
     */
    protected $enumerators;

    /**
     * @var EnumSet
     */
    protected $emptySet;

    /**
     * @var EnumSet
     */
    protected $fullSet;

    public function benchAddEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->add($enumerator);
        }
    }

    public function benchAddValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->add($value);
        }
    }

    public function benchWithEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->with($enumerator);
        }
    }

    public function benchWithValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->with($value);
        }
    }

    public function benchAddIterableEnumerators()
    {
        $this->emptySet->addIterable($this->enumerators);
    }

    public function benchAddIterableValues()
    {
        $this->emptySet->addIterable($this->values);
    }

    public function benchWithIterableEnumerators()
    {
        $this->emptySet->withIterable($this->enumerators);
    }

    public function benchWithIterableValues()
    {
        $this->emptySet->withIterable($this->values);
    }

    public function benchRemoveEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->remove($enumerator);
        }
    }

    public function benchRemoveValue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->remove($value);
        }
    }

    public function benchWithoutEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->without($enumerator);
        }
    }

    public function benchWithoutValue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->without($value);
        }
    }

    public function benchRemoveIterableEnumerators()
    {
        $this->fullSet->removeIterable($this->enumerators);
    }

    public function benchRemoveIterableValues()
    {
        $this->fullSet->removeIterable($this->values);
    }

    public function benchWithoutIterableEnumerators()
    {
        $this->fullSet->withoutIterable($this->enumerators);
    }

    public function benchWithoutIterableValues()
    {
        $this->fullSet->withoutIterable($this->values);
    }

    public function benchHasEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->has($enumerator);
        }
    }

    public function benchHasValue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->has($value);
        }
    }

    public function benchIterateFull()
    {
        \iterator_to_array($this->fullSet);
    }

    public function benchIterateEmpty()
    {
        \iterator_to_array($this->emptySet);
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

    public function benchGetValuesFull()
    {
        $this->fullSet->getValues();
    }

    public function benchGetNamesFull()
    {
        $this->fullSet->getNames();
    }

    public function benchGetEnumeratorsFull()
    {
        $this->fullSet->getEnumerators();
    }
}
