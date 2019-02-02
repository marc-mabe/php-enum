<?php

namespace MabeEnumTest;

use LogicException;
use MabeEnum\Enum;
use MabeEnumTest\TestAsset\SerializableEnum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

/**
 * Unit tests for the trait MabeEnum\EnumSerializableTrait
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumSerializableTraitTest extends TestCase
{
    public function testSerializeSerializableEnum()
    {
        $serialized = serialize(SerializableEnum::get(SerializableEnum::NIL));
        $this->assertInternalType('string', $serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(SerializableEnum::class, $unserialized);
    }

    public function testUnserializeFirstWillHoldTheSameInstance()
    {
        $serialized = serialize(SerializableEnum::get(SerializableEnum::STR));
        $this->assertInternalType('string', $serialized);

        // clear all instantiated instances so we can virtual test unserializing first
        $this->clearEnumeration(SerializableEnum::class);

        // First unserialize
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(SerializableEnum::class, $unserialized);

        // second instantiate
        $enum = SerializableEnum::get($unserialized->getValue());

        // check if it's the same instance
        $this->assertSame($enum, $unserialized);
    }

    public function testUnserializeThrowsRuntimeExceptionOnUnknownValue()
    {
        $this->expectException(RuntimeException::class);
        unserialize('C:' . strlen(SerializableEnum::class) . ':"' . SerializableEnum::class . '":11:{s:4:"test";}');
    }

    public function testUnserializeThrowsRuntimeExceptionOnInvalidValue()
    {
        $this->expectException(RuntimeException::class);
        unserialize('C:' . strlen(SerializableEnum::class) . ':"' . SerializableEnum::class . '":19:{O:8:"stdClass":0:{}}');
    }

    public function testUnserializeThrowsLogicExceptionOnChangingValue()
    {
        $this->expectException(LogicException::class);
        $enum = SerializableEnum::get(SerializableEnum::INT);
        $enum->unserialize(serialize(SerializableEnum::STR));
    }

    /**
     * Clears all instantiated enumerations and detected constants of the given enumerator
     * @param string $enumeration
     */
    private function clearEnumeration($enumeration)
    {
        $reflClass = new ReflectionClass($enumeration);
        while ($reflClass->getName() !== Enum::class) {
            $reflClass = $reflClass->getParentClass();
        }

        $reflPropInstances = $reflClass->getProperty('instances');
        $reflPropInstances->setAccessible(true);
        $reflPropInstances->setValue(null, array());
        $reflPropConstants = $reflClass->getProperty('constants');
        $reflPropConstants->setAccessible(true);
        $reflPropConstants->setValue(null, array());
    }
}
