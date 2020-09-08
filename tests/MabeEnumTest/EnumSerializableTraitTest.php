<?php

namespace MabeEnumTest;

use LogicException;
use MabeEnum\Enum;
use MabeEnumTest\TestAsset\ExtendedSerializableEnum;
use MabeEnumTest\TestAsset\SerializableEnum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

/**
 * Unit tests for the trait MabeEnum\EnumSerializableTrait
 *
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumSerializableTraitTest extends TestCase
{
    public function testSerializeSerializableEnum(): void
    {
        $serialized = serialize(SerializableEnum::get(SerializableEnum::NIL));
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(SerializableEnum::class, $unserialized);
    }

    public function testUnserializeFirstWillHoldTheSameInstance(): void
    {
        $serialized = serialize(SerializableEnum::get(SerializableEnum::STR));
        $this->assertIsString($serialized);

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

    public function testUnserializeThrowsRuntimeExceptionOnUnknownValue(): void
    {
        $this->expectException(RuntimeException::class);
        unserialize('C:' . strlen(SerializableEnum::class) . ':"' . SerializableEnum::class . '":11:{s:4:"test";}');
    }

    public function testUnserializeThrowsRuntimeExceptionOnInvalidValue(): void
    {
        $this->expectException(RuntimeException::class);
        unserialize('C:' . strlen(SerializableEnum::class) . ':"' . SerializableEnum::class . '":19:{O:8:"stdClass":0:{}}');
    }

    public function testUnserializeThrowsLogicExceptionOnChangingValue(): void
    {
        $enumInt = SerializableEnum::get(SerializableEnum::INT);
        $enumStrSer = SerializableEnum::STR()->__serialize();

        $this->expectException(LogicException::class);
        $enumInt->__unserialize($enumStrSer);
    }

    public function testInheritence(): void
    {
        $enum = ExtendedSerializableEnum::EXTENDED();

        $serialized = serialize($enum);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(ExtendedSerializableEnum::class, $unserialized);
        $this->assertSame($enum->getValue(), $unserialized->getValue());
    }

    public function testUnserializeFromPhp73(): void
    {
        $serialized = 'C:39:"MabeEnumTest\TestAsset\SerializableEnum":2:{N;}';
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(SerializableEnum::class, $unserialized);
        $this->assertNull($unserialized->getValue());
    }

    /**
     * Clears all instantiated enumerations and detected constants of the given enumerator
     * @param class-string<Enum> $enumeration
     */
    private function clearEnumeration($enumeration): void
    {
        $reflClass = new ReflectionClass($enumeration);
        while ($reflClass->getName() !== Enum::class) {
            /** @var ReflectionClass<Enum> $reflClass */
            $reflClass = $reflClass->getParentClass();
        }

        foreach ($reflClass->getProperties(ReflectionProperty::IS_STATIC) as $reflProp) {
            $reflProp->setAccessible(true);;
            $reflProp->setValue(null, []);
        }
    }
}
