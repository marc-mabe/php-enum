<?php

/**
 * Unit tests for the class MabeEnum_Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class MabeEnumTest_EnumTest extends PHPUnit_Framework_TestCase
{
    public function testGetNameReturnsConstantNameOfCurrentValue()
    {
        $enum = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $this->assertSame('ONE', $enum->getName());
    }

    public function testToStringMagicMethodReturnsValueAsString()
    {
        $enum = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $this->assertSame('1', $enum->__toString());
    }

    public function testEnumInheritance()
    {
        $enum = MabeEnumTest_TestAsset_EnumInheritance::get(MabeEnumTest_TestAsset_EnumInheritance::ONE);
        $this->assertSame(array(
            'ONE'         => 1,
            'TWO'         => 2,
            'INHERITANCE' => 'Inheritance'
        ), $enum::getConstants());
        $this->assertSame(MabeEnumTest_TestAsset_EnumInheritance::ONE, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());

        $enum = MabeEnumTest_TestAsset_EnumInheritance::get(MabeEnumTest_TestAsset_EnumInheritance::INHERITANCE);
        $this->assertSame(MabeEnumTest_TestAsset_EnumInheritance::INHERITANCE, $enum->getValue());
        $this->assertSame(2, $enum->getOrdinal());
    }

    public function testConstructorStrictValue()
    {
        $enum = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $this->assertSame(1, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());
    }

    public function testConstuctorNonStrictValue()
    {
        $enum = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get((string)MabeEnumTest_TestAsset_EnumWithoutDefaultValue::TWO);
        $this->assertSame(2, $enum->getValue());
        $this->assertSame(1, $enum->getOrdinal());
    }

    public function testConstructorInvalidValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get('unknown');
    }

    public function testCallingGetOrdinalTwoTimesWillResultTheSameValue()
    {
        $enum = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::TWO);
        $this->assertSame(1, $enum->getOrdinal());
        $this->assertSame(1, $enum->getOrdinal());
    }

    public function testInstantiateUsingMagicMethod()
    {
        $enum = MabeEnumTest_TestAsset_EnumInheritance::ONE();
        $this->assertInstanceOf('MabeEnumTest_TestAsset_EnumInheritance', $enum);
        $this->assertSame(MabeEnumTest_TestAsset_EnumInheritance::ONE, $enum->getValue());
    }

    public function testInstantiateUsingMagicMethodThrowsBadMethodCallException()
    {
        $this->setExpectedException('BadMethodCallException');
        MabeEnumTest_TestAsset_EnumInheritance::UNKNOWN();
    }

    public function testAmbuguousConstantsThrowsLogicException()
    {
        $this->setExpectedException('LogicException');
        MabeEnumTest_TestAsset_EnumAmbiguous::get(MabeEnumTest_TestAsset_EnumAmbiguous::AMBIGUOUS1);
    }

    public function testSingleton()
    {
        $enum1 = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::get(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $enum2 = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE();
        $this->assertSame($enum1, $enum2);
    }

    public function testClear()
    {
        $enum1 = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE();
        MabeEnumTest_TestAsset_EnumWithoutDefaultValue::clear();
        $enum2 = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE();
        $enum3 = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE();
        
        $this->assertNotSame($enum1, $enum2);
        $this->assertSame($enum2, $enum3);
    }

    public function testCloneNotCallableAndThrowsLogicException()
    {
        $enum = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE();

        $reflectionClass  = new ReflectionClass($enum);
        $reflectionMethod = $reflectionClass->getMethod('__clone');
        $this->assertTrue($reflectionMethod->isPrivate(), 'The method __clone must be private');
        $this->assertTrue($reflectionMethod->isFinal(), 'The method __clone must be final');

        $reflectionMethod->setAccessible(true);
        $this->setExpectedException('LogicException');
        $reflectionMethod->invoke($enum);
    }
}
