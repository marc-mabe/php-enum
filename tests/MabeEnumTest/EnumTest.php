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
        if (version_compare(PHP_VERSION, '5.3', '<')) {
            $this->markTestSkipped("Instantiating using magic method doesn't work for PHP < 5.3");
        }

        $enum = MabeEnumTest_TestAsset_EnumInheritance::ONE();
        $this->assertInstanceOf('MabeEnumTest_TestAsset_EnumInheritance', $enum);
        $this->assertSame(MabeEnumTest_TestAsset_EnumInheritance::ONE, $enum->getValue());
    }

    public function testInstantiateUsingMagicMethodThrowsBadMethodCallException()
    {
        if (version_compare(PHP_VERSION, '5.3', '<')) {
            $this->markTestSkipped("Instantiating using magic method doesn't work for PHP < 5.3");
        }

        $this->setExpectedException('BadMethodCallException');
        MabeEnumTest_TestAsset_EnumInheritance::UNKNOWN();
    }

    public function testAmbuguousConstantsThrowsLogicException()
    {
        $this->setExpectedException('LogicException');
        MabeEnumTest_TestAsset_EnumAmbiguous::get(MabeEnumTest_TestAsset_EnumAmbiguous::AMBIGUOUS1);
    }
}
