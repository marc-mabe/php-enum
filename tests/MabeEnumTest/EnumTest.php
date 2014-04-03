<?php

namespace MabeEnumTest;

use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumBasic2;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\EnumAmbiguous;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

/**
 * Unit tests for the class MabeEnum\Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumTest extends TestCase
{
    public function setUp()
    {
        EnumBasic::clear();
        EnumInheritance::clear();
    }

    public function testGetNameReturnsConstantNameOfCurrentValue()
    {
        $enum = EnumBasic::get(EnumBasic::ONE);
        $this->assertSame('ONE', $enum->getName());
    }

    public function testToStringMagicMethodReturnsName()
    {
        $enum = EnumBasic::get(EnumBasic::ONE);
        $this->assertSame('ONE', $enum->__toString());
    }

    public function testEnumInheritance()
    {
        $enum = EnumInheritance::get(EnumInheritance::ONE);
        $this->assertSame(array(
            'ONE'           => 1,
            'TWO'           => 2,
            'THREE'         => 3,
            'FOUR'          => 4,
            'FIVE'          => 5,
            'SIX'           => 6,
            'SEVEN'         => 7,
            'EIGHT'         => 8,
            'NINE'          => 9,
            'ZERO'          => 0,
            'FLOAT'         => 0.123,
            'STR'           => 'str',
            'STR_EMPTY'     => '',
            'NIL'           => null,
            'BOOLEAN_TRUE'  => true,
            'BOOLEAN_FALSE' => false,
            'INHERITANCE'   => 'Inheritance',
        ), $enum::getConstants());
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());

        $enum = EnumInheritance::get(EnumInheritance::INHERITANCE);
        $this->assertSame(EnumInheritance::INHERITANCE, $enum->getValue());
        $this->assertSame(16, $enum->getOrdinal());
    }

    public function testGetWithStrictValue()
    {
        $enum = EnumBasic::get(EnumBasic::ONE);
        $this->assertSame(1, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());
    }

    public function testGetWithNonStrictValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumBasic::get((string)EnumBasic::TWO);
    }

    public function testGetWithInvalidValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumBasic::get('unknown');
    }

    public function testGetWithInvalidTypeOfValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumBasic::get(array());
    }

    public function testGetByInstance()
    {
        $enum1 = EnumBasic::get(EnumBasic::ONE);
        $enum2 = EnumBasic::get($enum1);
        $this->assertSame($enum1, $enum2);
    }

    public function testGetByInheritInstance()
    {
        $enumInherit = EnumInheritance::get(EnumInheritance::ONE);
        $enum1       = EnumBasic::get(EnumBasic::ONE);
        $enum2       = EnumBasic::get($enumInherit);
        $this->assertSame($enum1, $enum2);
    }

    public function testGetByInheritInstanceThrowsInvalidArgumentExceptionOnUnknownValue()
    {
        $enumInherit = EnumInheritance::get(EnumInheritance::INHERITANCE);

        $this->setExpectedException('InvalidArgumentException');
        EnumBasic::get($enumInherit);
    }

    public function testGetByInstanceOfDifferentBaseThrowsInvalidArgumentException()
    {
        $enumDiff = EnumBasic2::get(EnumBasic2::ONE);

        $this->setExpectedException('InvalidArgumentException');
        EnumBasic::get($enumDiff);
    }

    public function testGetAllValues()
    {
        $constants = EnumBasic::getConstants();
        foreach ($constants as $name => $value) {
            $enum = EnumBasic::get($value);
            $this->assertSame($value, $enum->getValue());
            $this->assertSame($name, $enum->getName());
        }
    }

    public function testCallingGetOrdinalTwoTimesWillResultTheSameValue()
    {
        $enum = EnumBasic::get(EnumBasic::TWO);
        $this->assertSame(1, $enum->getOrdinal());
        $this->assertSame(1, $enum->getOrdinal());
    }

    public function testInstantiateUsingOrdinalNumber()
    {
        $enum = EnumInheritance::getByOrdinal(16);
        $this->assertSame(16, $enum->getOrdinal());
        $this->assertSame('INHERITANCE', $enum->getName());
    }

    public function testInstantiateUsingInvalidOrdinalNumberThrowsInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        EnumInheritance::getByOrdinal(17);
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
        EnumAmbiguous::get('unknown');
    }

    public function testSingleton()
    {
        $enum1 = EnumBasic::get(EnumBasic::ONE);
        $enum2 = EnumBasic::ONE();
        $this->assertSame($enum1, $enum2);
    }

    public function testClear()
    {
        $enum1 = EnumBasic::ONE();
        EnumBasic::clear();
        $enum2 = EnumBasic::ONE();
        $enum3 = EnumBasic::ONE();
        
        $this->assertNotSame($enum1, $enum2);
        $this->assertSame($enum2, $enum3);
    }

    public function testCloneNotCallableAndThrowsLogicException()
    {
        $enum = EnumBasic::ONE();

        $reflectionClass  = new ReflectionClass($enum);
        $reflectionMethod = $reflectionClass->getMethod('__clone');
        $this->assertTrue($reflectionMethod->isPrivate(), 'The method __clone must be private');
        $this->assertTrue($reflectionMethod->isFinal(), 'The method __clone must be final');

        $reflectionMethod->setAccessible(true);
        $this->setExpectedException('LogicException');
        $reflectionMethod->invoke($enum);
    }
}
