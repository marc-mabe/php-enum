<?php

/**
 * Unit tests for the class Mabe_Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class MabeTest_EnumTest extends PHPUnit_Framework_TestCase
{
    public function testEnumWithDefaultValue()
    {
        $enum = new EnumWithDefaultValue();

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
        $enum = new EnumWithoutDefaultValue(EnumWithoutDefaultValue::ONE);
        $this->assertSame('ONE', $enum->getName());
    }

    public function testToStringMagicMethodReturnsValueAsString()
    {
        $enum = new EnumWithoutDefaultValue(EnumWithoutDefaultValue::ONE);
        $this->assertSame('1', $enum->__toString());
    }

    public function testEnumWithNullAsDefaultValue()
    {
        $enum = new EnumWithNullAsDefaultValue();

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
        new EnumWithoutDefaultValue();
    }

    public function testEnumInheritance()
    {
        $enum = new EnumInheritance(EnumInheritance::ONE);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());

        $enum = new EnumInheritance(EnumInheritance::INHERITACE);
        $this->assertSame(EnumInheritance::INHERITACE, $enum->getValue());
    }

    public function testChangeValueOnConstructor()
    {
        $enum = new EnumWithoutDefaultValue(1);
        $this->assertSame(1, $enum->getValue());
    }

    public function testChangeValueOnConstructorThrowsInvalidArgumentExceptionOnStrictComparison()
    {
        $this->setExpectedException('InvalidArgumentException');
        $enum = new EnumWithoutDefaultValue('1');
    }

    public function testSetValue()
    {
        $enum = new EnumWithDefaultValue();
        $enum->setValue(2);

        $this->assertSame(2, $enum->getValue());
    }

    public function testSetValueThrowsInvalidArgumentExceptionOnStrictComparison()
    {
        $this->setExpectedException('InvalidArgumentException');
        $enum = new EnumWithDefaultValue();
        $enum->setValue('2');
    }
}

class EnumWithDefaultValue extends Mabe_Enum
{
    const ONE = 1;
    const TWO = 2;
    protected $value = 1;
}

class EnumWithNullAsDefaultValue extends Mabe_Enum
{
    const NONE = null;
    const ONE  = 1;
    const TWO  = 2;
}


class EnumWithoutDefaultValue extends Mabe_Enum
{
    const ONE = 1;
    const TWO = 2;
}

class EnumInheritance extends EnumWithoutDefaultValue
{
    const INHERITACE = 'Inheritance';
}

class EmptyEnum extends Mabe_Enum
{}
