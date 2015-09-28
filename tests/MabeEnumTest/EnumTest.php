<?php

namespace MabeEnumTest;

use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\EnumAmbiguous;
use MabeEnumTest\TestAsset\EnumExtendedAmbiguous;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

/**
 * Unit tests for the class MabeEnum\Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
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
        ), EnumInheritance::getConstants());

        $enum = EnumInheritance::get(EnumInheritance::ONE);
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

    public function testGetByExtendedInstanceOfKnownValue()
    {
        $enum = EnumInheritance::get(EnumInheritance::ONE);

        $this->setExpectedException('InvalidArgumentException');
        EnumBasic::get($enum);
    }

    public function testGetEnumerators()
    {
        $constants   = EnumInheritance::getConstants();
        $enumerators = EnumInheritance::getEnumerators();
        $count       = count($enumerators);

        $this->assertSame(count($constants), $count);
        for ($i = 0; $i < $count; ++$i) {
            $this->assertArrayHasKey($i, $enumerators);
            $this->assertInstanceOf('MabeEnumTest\TestAsset\EnumInheritance', $enumerators[$i]);

            $enumerator = $enumerators[$i];
            $this->assertArrayHasKey($enumerator->getName(), $constants);
            $this->assertSame($constants[$enumerator->getName()], $enumerator->getValue());
        }
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

    public function testIsBasic()
    {
        $enum = EnumBasic::ONE();

        // by value
        $this->assertTrue($enum->is(EnumBasic::ONE));   // same
        $this->assertFalse($enum->is('1'));             // wrong value by strict comparison

        // by instance
        $this->assertTrue($enum->is(EnumBasic::ONE()));        // same
        $this->assertFalse($enum->is(EnumBasic::TWO()));       // different enumerators
        $this->assertFalse($enum->is(EnumInheritance::ONE())); // different enumeration type
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

    public function testAmbiguousConstantsThrowsLogicException()
    {
        $this->setExpectedException('LogicException');
        EnumAmbiguous::get('unknown');
    }

    public function testExtendedAmbiguousCanstantsThrowsLogicException()
    {
        $this->setExpectedException('LogicException');
        EnumExtendedAmbiguous::get('unknown');
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

    public function testNotSerializable()
    {
        $enum = EnumBasic::ONE();

        $this->setExpectedException('LogicException');
        serialize($enum);
    }

    public function testNotUnserializable()
    {
        $this->setExpectedException('LogicException');
        unserialize("O:32:\"MabeEnumTest\TestAsset\EnumBasic\":0:{}");
    }

    public function testHas()
    {
        $enum = EnumBasic::ONE();

        $this->assertFalse($enum->has('invalid'));
        $this->assertTrue($enum->has(1));
        $this->assertTrue($enum->has(EnumBasic::ONE()));
        $this->assertTrue($enum->has(EnumBasic::ONE));
    }
}
