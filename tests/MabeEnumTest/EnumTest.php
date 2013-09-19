<?php

namespace MabeEnumTest;

use MabeEnumTest\TestAsset\EnumWithoutDefaultValue;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\EnumAmbiguous;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

/**
 * Unit tests for the class MabeEnum\Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumTest extends TestCase
{
    public function testGetNameReturnsConstantNameOfCurrentValue()
    {
        $enum = EnumWithoutDefaultValue::get(EnumWithoutDefaultValue::ONE);
        $this->assertSame('ONE', $enum->getName());
    }

    public function testToStringMagicMethodReturnsValueAsString()
    {
        $enum = EnumWithoutDefaultValue::get(EnumWithoutDefaultValue::ONE);
        $this->assertSame('1', $enum->__toString());
    }

    public function testEnumInheritance()
    {
        $enum = EnumInheritance::get(EnumInheritance::ONE);
        $this->assertSame(array(
            'ONE'         => 1,
            'TWO'         => 2,
            'INHERITANCE' => 'Inheritance'
        ), $enum::getConstants());
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());

        $enum = EnumInheritance::get(EnumInheritance::INHERITANCE);
        $this->assertSame(EnumInheritance::INHERITANCE, $enum->getValue());
        $this->assertSame(2, $enum->getOrdinal());
    }

    public function testConstructorStrictValue()
    {
        $enum = EnumWithoutDefaultValue::get(EnumWithoutDefaultValue::ONE);
        $this->assertSame(1, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());
    }

    public function testConstuctorNonStrictValue()
    {
        $enum = EnumWithoutDefaultValue::get((string)EnumWithoutDefaultValue::TWO);
        $this->assertSame(2, $enum->getValue());
        $this->assertSame(1, $enum->getOrdinal());
    }

    public function testConstructorInvalidValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumWithoutDefaultValue::get('unknown');
    }

    public function testCallingGetOrdinalTwoTimesWillResultTheSameValue()
    {
        $enum = EnumWithoutDefaultValue::get(EnumWithoutDefaultValue::TWO);
        $this->assertSame(1, $enum->getOrdinal());
        $this->assertSame(1, $enum->getOrdinal());
    }

    public function testInstantiateUsingOrdinalNumber()
    {
        $enum = EnumInheritance::getByOrdinal(2);
        $this->assertSame(2, $enum->getOrdinal());
        $this->assertSame('INHERITANCE', $enum->getName());
    }

    public function testInstantiateUsingInvalidOrdinalNumberThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumInheritance::getByOrdinal(3);
    }

    public function testInstantiateByName()
    {
        $enum = EnumInheritance::getByName('ONE');
        $this->assertInstanceOf('MabeEnumTest\TestAsset\EnumInheritance', $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
    }

    public function testInstantiateByUnknownNameThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumInheritance::getByName('UNKNOWN');
    }

    public function testInstantiateUsingMagicMethod()
    {
        $enum = EnumInheritance::ONE();
        $this->assertInstanceOf('MabeEnumTest\TestAsset\EnumInheritance', $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
    }

    public function testAmbuguousConstantsThrowsLogicException()
    {
        $this->setExpectedException('LogicException');
        EnumAmbiguous::get(EnumAmbiguous::AMBIGUOUS1);
    }

    public function testSingleton()
    {
        $enum1 = EnumWithoutDefaultValue::get(EnumWithoutDefaultValue::ONE);
        $enum2 = EnumWithoutDefaultValue::ONE();
        $this->assertSame($enum1, $enum2);
    }

    public function testClear()
    {
        $enum1 = EnumWithoutDefaultValue::ONE();
        EnumWithoutDefaultValue::clear();
        $enum2 = EnumWithoutDefaultValue::ONE();
        $enum3 = EnumWithoutDefaultValue::ONE();
        
        $this->assertNotSame($enum1, $enum2);
        $this->assertSame($enum2, $enum3);
    }

    public function testCloneNotCallableAndThrowsLogicException()
    {
        $enum = EnumWithoutDefaultValue::ONE();

        $reflectionClass  = new ReflectionClass($enum);
        $reflectionMethod = $reflectionClass->getMethod('__clone');
        $this->assertTrue($reflectionMethod->isPrivate(), 'The method __clone must be private');
        $this->assertTrue($reflectionMethod->isFinal(), 'The method __clone must be final');

        $reflectionMethod->setAccessible(true);
        $this->setExpectedException('LogicException');
        $reflectionMethod->invoke($enum);
    }
}
