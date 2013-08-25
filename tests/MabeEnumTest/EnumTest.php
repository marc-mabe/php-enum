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
    public function testEnumWithDefaultValue()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithDefaultValue();

        $this->assertSame(
            array(
                'ONE' => 1,
                'TWO' => 2,
            ),
            $enum->getConstants()
        );

        $this->assertSame(1, $enum->getValue());
        $this->assertSame('1', $enum->__toString());

        $this->assertSame('ONE', $enum->getName());
    }

    public function testGetNameReturnsConstantNameOfCurrentValue()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $this->assertSame('ONE', $enum->getName());
    }

    public function testToStringMagicMethodReturnsValueAsString()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $this->assertSame('1', $enum->__toString());
    }

    public function testEnumWithNullAsDefaultValue()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithNullAsDefaultValue();

        $this->assertSame(array(
            'NONE' => null,
            'ONE'  => 1,
            'TWO'  => 2,
        ), $enum->getConstants());

        $this->assertNull($enum->getValue());
    }

    public function testEnumWithoutDefaultValue()
    {
        $this->setExpectedException('InvalidArgumentException');
        new MabeEnumTest_TestAsset_EnumWithoutDefaultValue();
    }

    public function testEnumInheritance()
    {
        $enum = new MabeEnumTest_TestAsset_EnumInheritance(MabeEnumTest_TestAsset_EnumInheritance::ONE);
        $this->assertSame(MabeEnumTest_TestAsset_EnumInheritance::ONE, $enum->getValue());

        $enum = new MabeEnumTest_TestAsset_EnumInheritance(MabeEnumTest_TestAsset_EnumInheritance::INHERITACE);
        $this->assertSame(MabeEnumTest_TestAsset_EnumInheritance::INHERITACE, $enum->getValue());
    }

    public function testChangeValueOnConstructor()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(1);
        $this->assertSame(1, $enum->getValue());
    }

    public function testChangeValueOnConstructorThrowsInvalidArgumentExceptionOnStrictComparison()
    {
        $this->setExpectedException('InvalidArgumentException');
        $enum = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue('1');
    }

    public function testSetValue()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithDefaultValue();
        $enum->setValue(2);

        $this->assertSame(2, $enum->getValue());
    }

    public function testSetValueThrowsInvalidArgumentExceptionOnStrictComparison()
    {
        $this->setExpectedException('InvalidArgumentException');
        $enum = new MabeEnumTest_TestAsset_EnumWithDefaultValue();
        $enum->setValue('2');
    }

    public function testSetName()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithDefaultValue();
        $enum->setValue(2);
        $enum->setName('ONE');
        $this->assertEquals(1, $enum->getValue());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetInvalidName()
    {
        $enum = new MabeEnumTest_TestAsset_EnumWithDefaultValue();
        $enum->setName('FOO');
    }
}
