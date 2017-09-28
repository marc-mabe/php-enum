<?php

namespace MabeEnumBench;

use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\Enum66;

/**
 * Benchmark of EnumMap used with an enumeration of 66 enumerators.
 *
 * @BeforeMethods({"init"})
 * @Revs(2000)
 * @Iterations(25)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMapBench
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
     * @var EnumMap
     */
    private $emptyMap;

    /**
     * @var EnumMap
     */
    private $fullMap;

    /**
     * Will be called before every subject
     */
    public function init()
    {
        $this->values      = Enum66::getValues();
        $this->enumerators = Enum66::getEnumerators();

        $this->emptyMap = new EnumMap(Enum66::class);
        $this->fullMap  = new EnumMap(Enum66::class);
        foreach ($this->enumerators as $enumerator) {
            $this->fullMap->offsetSet($enumerator);
        }
    }

    public function benchOffsetSetEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->emptyMap->offsetSet($enumerator);
        }
    }

    public function benchOffsetSetValue()
    {
        foreach ($this->values as $value) {
            $this->emptyMap->offsetSet($value);
        }
    }

    public function benchOffsetUnsetEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullMap->offsetUnset($enumerator);
        }
    }

    public function benchOffsetUnsetValue()
    {
        foreach ($this->values as $value) {
            $this->fullMap->offsetUnset($value);
        }
    }

    public function benchOffsetExistsEnumeratorTrue()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullMap->offsetExists($enumerator);
        }
    }

    public function benchOffsetExistsEnumeratorFalse()
    {
        foreach ($this->enumerators as $enumerator) {
            $this->fullMap->offsetExists($enumerator);
        }
    }

    public function benchOffsetExistsValueTrue()
    {
        foreach ($this->values as $value) {
            $this->fullMap->offsetExists($value);
        }
    }

    public function benchOffsetExistsValueFalse()
    {
        foreach ($this->values as $value) {
            $this->fullMap->offsetExists($value);
        }
    }

    public function benchIterateFull()
    {
        foreach ($this->fullMap as $enumerator => $_) {
            $enumerator->getValue();
        }
    }

    public function benchIterateEmpty()
    {
        foreach ($this->emptyMap as $enumerator => $_) {
            $enumerator->getValue();
        }
    }

    public function benchCountFull()
    {
        $this->fullMap->count();
    }

    public function benchCountEmpty()
    {
        $this->emptyMap->count();
    }
}
