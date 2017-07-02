<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use LogicException;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\EnumAmbiguous;
use MabeEnumTest\TestAsset\EnumExtendedAmbiguous;
use MabeEnumTest\TestAsset\ConstVisibilityEnum;
use MabeEnumTest\TestAsset\ConstVisibilityEnumExtended;
use MabeEnumTest\TestAsset\SerializableEnum;
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
        $this->setExpectedException(InvalidArgumentException::class);
        EnumBasic::get((string)EnumBasic::TWO);
    }

    public function testGetWithInvalidValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        EnumBasic::get('unknown');
    }

    public function testGetWithInvalidTypeOfValueThrowsInvalidArgumentException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
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

        $this->setExpectedException(InvalidArgumentException::class);
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
            $this->assertInstanceOf(EnumInheritance::class, $enumerators[$i]);

            $enumerator = $enumerators[$i];
            $this->assertArrayHasKey($enumerator->getName(), $constants);
            $this->assertSame($constants[$enumerator->getName()], $enumerator->getValue());
        }
    }

    public function testGetValues()
    {
        $expectedValues = array_values(EnumInheritance::getConstants());
        $values         = EnumInheritance::getValues();
        $count          = count($values);

        $this->assertSame(count($expectedValues), $count);
        for ($i = 0; $i < $count; ++$i) {
            $this->assertArrayHasKey($i, $values);
            $this->assertSame($expectedValues[$i], $values[$i]);
        }
    }

    public function testGetNames()
    {
        $expectedNames = array_keys(EnumInheritance::getConstants());
        $names         = EnumInheritance::getNames();
        $count         = count($names);

        $this->assertSame(count($expectedNames), $count);
        for ($i = 0; $i < $count; ++$i) {
            $this->assertArrayHasKey($i, $names);
            $this->assertSame($expectedNames[$i], $names[$i]);
        }
    }

    public function testGetOrdinals()
    {
        $constants = EnumInheritance::getConstants();
        $ordinals  = EnumInheritance::getOrdinals();
        $count     = count($ordinals);

        $this->assertSame(count($constants), $count);
        for ($i = 0; $i < $count; ++$i) {
            $this->assertArrayHasKey($i, $ordinals);
            $this->assertSame($i, $ordinals[$i]);
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
        $enum = EnumInheritance::byOrdinal(16);
        $this->assertSame(16, $enum->getOrdinal());
        $this->assertSame('INHERITANCE', $enum->getName());
    }

    public function testInstantiateUsingInvalidOrdinalNumberThrowsInvalidArgumentException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        EnumInheritance::byOrdinal(17);
    }

    public function testInstantiateByName()
    {
        $enum = EnumInheritance::byName('ONE');
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
    }

    public function testInstantiateByUnknownNameThrowsInvalidArgumentException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        EnumInheritance::byName('UNKNOWN');
    }

    public function testInstantiateUsingMagicMethod()
    {
        $enum = EnumInheritance::ONE();
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
    }

    public function testAmbiguousConstantsThrowsLogicException()
    {
        $this->setExpectedException(LogicException::class);
        EnumAmbiguous::get('unknown');
    }

    public function testExtendedAmbiguousCanstantsThrowsLogicException()
    {
        $this->setExpectedException(LogicException::class);
        EnumExtendedAmbiguous::get('unknown');
    }

    public function testSingleton()
    {
        $enum1 = EnumBasic::get(EnumBasic::ONE);
        $enum2 = EnumBasic::ONE();
        $this->assertSame($enum1, $enum2);
    }

    public function testCloneNotCallableAndThrowsLogicException()
    {
        $enum = EnumBasic::ONE();

        $reflectionClass  = new ReflectionClass($enum);
        $reflectionMethod = $reflectionClass->getMethod('__clone');
        $this->assertTrue($reflectionMethod->isPrivate(), 'The method __clone must be private');
        $this->assertTrue($reflectionMethod->isFinal(), 'The method __clone must be final');

        $reflectionMethod->setAccessible(true);
        $this->setExpectedException(LogicException::class);
        $reflectionMethod->invoke($enum);
    }

    public function testNotSerializable()
    {
        $enum = EnumBasic::ONE();

        $this->setExpectedException(LogicException::class);
        serialize($enum);
    }

    public function testNotUnserializable()
    {
        $this->setExpectedException(LogicException::class);
        unserialize('O:' . strlen(EnumBasic::class) . ':"' . EnumBasic::class . '":0:{}');
    }

    public function testHas()
    {
        $enum = EnumBasic::ONE();

        $this->assertFalse($enum->has('invalid'));
        $this->assertFalse($enum->has(EnumInheritance::ONE()));
        $this->assertTrue($enum->has(EnumBasic::ONE()));
        $this->assertTrue($enum->has(EnumBasic::ONE));
    }
    
    public function testConstVisibility()
    {
        if (PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('This test is for PHP-7.1 and upper only');
        }

        $constants = ConstVisibilityEnum::getConstants();
        $this->assertSame(array(
            'IPUB' => ConstVisibilityEnum::IPUB,
            'PUB'  => ConstVisibilityEnum::PUB,
        ), $constants);
    }
    
    public function testConstVisibilityExtended()
    {
        if (PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('This test is for PHP-7.1 and upper only');
        }

        $constants = ConstVisibilityEnumExtended::getConstants();
        $this->assertSame(array(
            'IPUB'  => ConstVisibilityEnumExtended::IPUB,
            'PUB'   => ConstVisibilityEnumExtended::PUB,
            'IPUB2' => ConstVisibilityEnumExtended::IPUB2,
            'PUB2'  => ConstVisibilityEnumExtended::PUB2,
        ), $constants);
    }

    public function testIsSerializableIssue()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('This test is for PHP-5.4 and upper only');
        }

        $enum1 = SerializableEnum::INT();
        $enum2 = unserialize(serialize($enum1));

        $this->assertFalse($enum1 === $enum2, 'Wrong test implementation');
        $this->assertTrue($enum1->is($enum2), 'Two different instances of exact the same enumerator should be equal');
    }
}
