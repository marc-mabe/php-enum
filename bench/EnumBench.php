<?php

namespace MabeEnumBench;

use MabeEnumTest\TestAsset\Enum66;

/**
 * Benchmark of abstract class Enum tested with enumeration of 66 enumerators.
 *
 * @BeforeMethods({"init"})
 * @Revs(2500)
 * @Iterations(25)
 */
class EnumBench
{
    /**
     * @var string[]
     */
    private $names;

    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @var int[]
     */
    private $ordinals;

    /**
     * @var Enum66[]
     */
    private $enumerators;

    /**
     * Will be called before every subject
     */
    public function init()
    {
        $this->names       = Enum66::getNames();
        $this->values      = Enum66::getValues();
        $this->ordinals    = Enum66::getOrdinals();
        $this->enumerators = Enum66::getEnumerators();
    }

    public function benchGetName()
    {
        foreach ($this->enumerators as $enumerator) {
            $enumerator->getName();
        }
    }

    public function benchGetValue()
    {
        foreach ($this->enumerators as $enumerator) {
            $enumerator->getValue();
        }
    }

    public function benchGetOrdinal()
    {
        foreach ($this->enumerators as $enumerator) {
            $enumerator->getOrdinal();
        }
    }

    public function benchIsByEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            $enumerator->is($enumerator);
        }
    }

    public function benchIsByValue()
    {
        foreach ($this->enumerators as $enumerator) {
            $enumerator->is($enumerator->getValue());
        }
    }

    public function benchGetConstants()
    {
        Enum66::getConstants();
    }

    public function benchGetValues()
    {
        Enum66::getValues();
    }

    public function benchGetNames()
    {
        Enum66::getNames();
    }

    public function benchGetOrdinals()
    {
        Enum66::getOrdinals();
    }

    public function benchGetEnumerators()
    {
        Enum66::getEnumerators();
    }

    public function benchByValue()
    {
        foreach ($this->values as $value) {
            Enum66::byValue($value);
        }
    }

    public function benchByName()
    {
        foreach ($this->names as $name) {
            Enum66::byName($name);
        }
    }

    public function benchByOrdinal()
    {
        foreach ($this->ordinals as $ord) {
            Enum66::byOrdinal($ord);
        }
    }

    public function benchGetByValues()
    {
        foreach ($this->values as $value) {
            Enum66::get($value);
        }
    }

    public function benchGetByEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            Enum66::get($enumerator);
        }
    }

    public function benchGetByCallStatic()
    {
        foreach ($this->names as $name) {
            Enum66::$name();
        }
    }

    public function benchHasByEnumerator()
    {
        foreach ($this->enumerators as $enumerator) {
            Enum66::has($enumerator);
        }
    }

    public function benchHasByValue()
    {
        foreach ($this->values as $value) {
            Enum66::has($value);
        }
    }
}
