<?php

namespace MabeEnumBench;

use MabeEnum\Enum;
use MabeEnumTest\TestAsset\Enum66;
use ReflectionClass;
use ReflectionProperty;

/**
 * Benchmark of abstract class Enum tested with enumeration of 66 enumerators.
 *
 * @BeforeMethods({"init"})
 * @Revs(2500)
 * @Iterations(25)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumBench
{
    /**
     * @var ReflectionProperty[]
     */
    private $enumPropsRefl;
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
        $enumRefl = new ReflectionClass(Enum::class);
        $enumPropsRefl = $enumRefl->getProperties(ReflectionProperty::IS_STATIC);
        foreach ($enumPropsRefl as $enumPropRefl) {
            $enumPropRefl->setAccessible(true);
            $this->enumPropsRefl[$enumPropRefl->getName()] = $enumPropRefl;
        }

        $this->names       = Enum66::getNames();
        $this->values      = Enum66::getValues();
        $this->ordinals    = Enum66::getOrdinals();
        $this->enumerators = Enum66::getEnumerators();
    }

    private function destructEnumerations()
    {
        foreach ($this->enumPropsRefl as $enumPropRefl) {
            $enumPropRefl->setValue([]);
        }
    }

    private function destructEnumerationInstances()
    {
        $this->enumPropsRefl['instances']->setValue([]);
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

    public function benchDetectConstants()
    {
        $this->destructEnumerations();
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

    public function benchByValueAndInitialize()
    {
        foreach ($this->values as $value) {
            $this->destructEnumerations();
            Enum66::byValue($value);
        }
    }

    public function benchByValueAndInstantiate()
    {
        $this->destructEnumerationInstances();
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

    public function benchByNameAndInitialize()
    {
        foreach ($this->names as $name) {
            $this->destructEnumerations();
            Enum66::byName($name);
        }
    }

    public function benchByNameAndInstantiate()
    {
        $this->destructEnumerationInstances();
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

    public function benchByOrdinalAndInitialize()
    {
        foreach ($this->ordinals as $ord) {
            $this->destructEnumerations();
            Enum66::byOrdinal($ord);
        }
    }

    public function benchByOrdinalAndInstantiate()
    {
        $this->destructEnumerationInstances();
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
