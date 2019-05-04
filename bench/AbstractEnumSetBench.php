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
 * @copyright Copyright (c) 2017 Marc Bennewitz
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

    public function benchAttachEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptySet->attach($enumerator);
        }
    }

    public function benchAttachValue()
    {
        foreach ($this->values as $value) {
            $this->emptySet->attach($value);
        }
    }

    public function benchDetachEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->detach($enumerator);
        }
    }

    public function benchDetachValue()
    {
        foreach ($this->values as $value) {
            $this->fullSet->detach($value);
        }
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
