<?php

namespace MabeEnumTest;

use MabeEnumTest\TestAsset\SerializableEnum;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Unit tests for the trait MabeEnum\EnumSerializableTrait
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSerializableTraitTest extends TestCase
{
    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->markTestSkipped('Traits require PHP-5.4');
        }

        SerializableEnum::clear();
    }

    public function testSerializeSerializableEnum()
    {
        $serialized = serialize(SerializableEnum::get(SerializableEnum::NIL));
        $this->assertInternalType('string', $serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf('MabeEnumTest\TestAsset\SerializableEnum', $unserialized);
    }

    public function testUnserializeFirstWillHoldTheSameInstance()
    {
        $serialized = serialize(SerializableEnum::get(SerializableEnum::STR));
        $this->assertInternalType('string', $serialized);

        // clear all instantiated instances so we can virtual test unserializing first
        SerializableEnum::clear();

        // First unserialize
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf('MabeEnumTest\TestAsset\SerializableEnum', $unserialized);

        // second instantiate
        $enum = SerializableEnum::get($unserialized->getValue());

        // check if it's the same instance
        $this->assertSame($enum, $unserialized);
    }

    public function testUnserializeThrowsRuntimeExceptionOnUnknownValue()
    {
        $this->setExpectedException('RuntimeException');
        unserialize('C:39:"MabeEnumTest\TestAsset\SerializableEnum":11:{s:4:"test";}');
    }

    public function testUnserializeThrowsRuntimeExceptionOnInvalidValue()
    {
        $this->setExpectedException('RuntimeException');
        unserialize('C:39:"MabeEnumTest\TestAsset\SerializableEnum":19:{O:8:"stdClass":0:{}}');
    }

    public function testUnserializeThrowsLogicExceptionOnChangingValue()
    {
        $this->setExpectedException('LogicException');
        $enum = SerializableEnum::get(SerializableEnum::INT);
        $enum->unserialize(serialize(SerializableEnum::STR));
    }
}
