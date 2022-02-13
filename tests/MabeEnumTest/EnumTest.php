<?php

namespace MabeEnumTest;

use AssertionError;
use InvalidArgumentException;
use LogicException;
use MabeEnum\Enum;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\EnumAmbiguous;
use MabeEnumTest\TestAsset\EnumExtendedAmbiguous;
use MabeEnumTest\TestAsset\ConstVisibilityEnum;
use MabeEnumTest\TestAsset\ConstVisibilityEnumExtended;
use MabeEnumTest\TestAsset\SerializableEnum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Unit tests for the class MabeEnum\Enum
 *
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumTest extends TestCase
{
    public function setUp(): void
    {
        $this->resetStaticEnumProps();
    }

    /**
     * Un-initialize all known enumerations
     */
    private function resetStaticEnumProps(): void
    {
        $enumRefl = new ReflectionClass(Enum::class);
        $enumPropsRefl = $enumRefl->getProperties(ReflectionProperty::IS_STATIC);
        foreach ($enumPropsRefl as $enumPropRefl) {
            $enumPropRefl->setAccessible(true);
            $enumPropRefl->setValue([]);
        }
    }

    /**
     * Test that Enumeration getters works fine after Enum::byName()
     * as Enum::byName() does not initialize the enumeration directly
     */
    public function testByNameEnumGettersWorks(): void
    {
        $this->resetStaticEnumProps();
        $this->assertSame(EnumBasic::ONE, EnumBasic::byName('ONE')->getValue());

        $this->resetStaticEnumProps();
        $this->assertSame('ONE', EnumBasic::byName('ONE')->getName());

        $this->resetStaticEnumProps();
        $this->assertSame(0, EnumBasic::byName('ONE')->getOrdinal());
    }

    public function testGetNameReturnsConstantNameOfCurrentValue(): void
    {
        $enum = EnumBasic::get(EnumBasic::ONE);
        $this->assertSame('ONE', $enum->getName());
    }

    public function testToStringMagicMethodReturnsName(): void
    {
        $enum = EnumBasic::get(EnumBasic::ONE);
        $this->assertSame('ONE', $enum->__toString());
    }

    public function testEnumInheritance(): void
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
            'ARR'           => EnumInheritance::ARR,
            'FLOAT'         => EnumInheritance::FLOAT,
            'STR'           => EnumInheritance::STR,
            'STR_EMPTY'     => EnumInheritance::STR_EMPTY,
            'NIL'           => EnumInheritance::NIL,
            'BOOLEAN_TRUE'  => EnumInheritance::BOOLEAN_TRUE,
            'BOOLEAN_FALSE' => EnumInheritance::BOOLEAN_FALSE,
            'INHERITANCE'   => 'Inheritance',
        ), EnumInheritance::getConstants());

        $enum = EnumInheritance::get(EnumInheritance::ONE);
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());

        $enum = EnumInheritance::get(EnumInheritance::INHERITANCE);
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::INHERITANCE, $enum->getValue());
        $this->assertSame(17, $enum->getOrdinal());

        $enum = EnumInheritance::ONE();
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());

        $enum = EnumInheritance::INHERITANCE();
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::INHERITANCE, $enum->getValue());
        $this->assertSame(17, $enum->getOrdinal());
    }

    public function testGetWithStrictValue(): void
    {
        $enum = EnumBasic::get(EnumBasic::ONE);
        $this->assertSame(1, $enum->getValue());
        $this->assertSame(0, $enum->getOrdinal());
    }

    public function testGetWithNonStrictValueThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown value '2' for enumeration MabeEnumTest\\TestAsset\\EnumBasic");
        EnumBasic::get((string)EnumBasic::TWO);
    }

    public function testGetWithInvalidValueThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown value 'unknown' for enumeration MabeEnumTest\\TestAsset\\EnumBasic");
        EnumBasic::get('unknown');
    }

    public function testGetWithInvalidArrayValueThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown value of type array for enumeration MabeEnumTest\\TestAsset\\EnumBasic");
        EnumBasic::get(array());
    }

    public function testGetWithInvalidTypeOfValueThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Unknown value of type " . get_class($this) . " for enumeration MabeEnumTest\\TestAsset\\EnumBasic"
        );
        EnumBasic::get($this);
    }

    public function testGetByInstance(): void
    {
        $enum1 = EnumBasic::get(EnumBasic::ONE);
        $enum2 = EnumBasic::get($enum1);
        $this->assertSame($enum1, $enum2);
    }

    public function testGetByExtendedInstanceOfKnownValue(): void
    {
        $enum = EnumInheritance::get(EnumInheritance::ONE);

        $this->expectException(InvalidArgumentException::class);
        EnumBasic::get($enum);
    }

    public function testGetEnumeratorsConstansAlreadyDetected(): void
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

    public function testGetEnumeratorsConstansNotDetected(): void
    {
        $enumerators = EnumInheritance::getEnumerators();
        $constants   = EnumInheritance::getConstants();
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

    public function testGetValues(): void
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

    public function testGetNamesConstantsAlreadyDetected(): void
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

    public function testGetNamesConstantsNotDetected(): void
    {
        $names         = EnumInheritance::getNames();
        $expectedNames = array_keys(EnumInheritance::getConstants());
        $count         = count($names);

        $this->assertSame(count($expectedNames), $count);
        for ($i = 0; $i < $count; ++$i) {
            $this->assertArrayHasKey($i, $names);
            $this->assertSame($expectedNames[$i], $names[$i]);
        }
    }

    public function testGetOrdinals(): void
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

    public function testGetAllValues(): void
    {
        $constants = EnumBasic::getConstants();
        foreach ($constants as $name => $value) {
            $enum = EnumBasic::get($value);
            $this->assertSame($value, $enum->getValue());
            $this->assertSame($name, $enum->getName());
        }
    }

    public function testIsBasic(): void
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

    public function testCallingGetOrdinalTwoTimesWillResultTheSameValue(): void
    {
        $enum = EnumBasic::get(EnumBasic::TWO);
        $this->assertSame(1, $enum->getOrdinal());
        $this->assertSame(1, $enum->getOrdinal());
    }

    public function testInstantiateUsingOrdinalNumber(): void
    {
        $enum = EnumInheritance::byOrdinal(17);
        $this->assertSame(17, $enum->getOrdinal());
        $this->assertSame('INHERITANCE', $enum->getName());
    }

    public function testInstantiateUsingInvalidOrdinalNumberThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EnumInheritance::byOrdinal(18);
    }

    public function testInstantiateByName(): void
    {
        $enum = EnumInheritance::byName('ONE');
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
    }

    public function testInstantiateByUnknownNameThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        EnumInheritance::byName('UNKNOWN');
    }

    public function testInstantiateUsingMagicMethod(): void
    {
        $enum = EnumInheritance::ONE();
        $this->assertInstanceOf(EnumInheritance::class, $enum);
        $this->assertSame(EnumInheritance::ONE, $enum->getValue());
    }

    public function testEnabledAssertAmbiguousEnumeratorValues(): void
    {
        $this->expectException(AssertionError::class);
        $this->expectExceptionMessage('Ambiguous enumerator values detected for ' . EnumAmbiguous::class);

        EnumAmbiguous::get('unknown');
    }

    public function testByNameAmbiguousEnumeratorValues(): void
    {
        $this->expectException(AssertionError::class);
        $this->expectExceptionMessage('Ambiguous enumerator values detected for ' . EnumAmbiguous::class);

        EnumAmbiguous::byName('AMBIGUOUS_INT1');
    }

    public function testExtendedEnabledAssertAmbiguousEnumeratorValues(): void
    {
        $this->expectException(AssertionError::class);
        $this->expectExceptionMessage('Ambiguous enumerator values detected for ' . EnumExtendedAmbiguous::class);

        EnumExtendedAmbiguous::get('unknown');
    }

    public function testSingleton(): void
    {
        $enum1 = EnumBasic::get(EnumBasic::ONE);
        $enum2 = EnumBasic::ONE();
        $this->assertSame($enum1, $enum2);
    }

    public function testCloneNotCallableAndThrowsLogicException(): void
    {
        $enum = EnumBasic::ONE();

        $reflectionClass  = new ReflectionClass($enum);
        $reflectionMethod = $reflectionClass->getMethod('__clone');
        $this->assertTrue($reflectionMethod->isFinal(), 'The method __clone must be final');

        $this->expectException(LogicException::class);
        clone $enum;
    }

    public function testNotSerializable(): void
    {
        $enum = EnumBasic::ONE();

        $this->expectException(LogicException::class);
        serialize($enum);
    }

    public function testNotUnserializable(): void
    {
        $this->expectException(LogicException::class);
        unserialize('O:' . strlen(EnumBasic::class) . ':"' . EnumBasic::class . '":0:{}');
    }

    public function testHas(): void
    {
        $this->assertFalse(EnumBasic::has('invalid'));
        $this->assertFalse(EnumBasic::has(EnumInheritance::ONE()));
        $this->assertTrue(EnumBasic::has(EnumBasic::ONE()));
        $this->assertTrue(EnumBasic::has(EnumBasic::ONE));
    }

    public function testHasName(): void
    {
        $this->assertFalse(EnumBasic::hasName(''));
        $this->assertFalse(EnumBasic::hasName('str'));
        $this->assertTrue(EnumBasic::hasName('ONE'));
        $this->assertTrue(EnumBasic::hasName('STR'));
    }

    public function testHasValue(): void
    {
        $enum = EnumBasic::ONE();

        $this->assertFalse(EnumBasic::hasValue('invalid'));
        $this->assertFalse(EnumBasic::hasValue(EnumInheritance::ONE()));
        $this->assertFalse(EnumBasic::hasValue(EnumBasic::ONE()));
        $this->assertTrue(EnumBasic::hasValue(EnumBasic::ONE));
    }

    public function testConstVisibility(): void
    {
        $constants = ConstVisibilityEnum::getConstants();
        $this->assertSame(array(
            'IPUB' => ConstVisibilityEnum::IPUB,
            'PUB'  => ConstVisibilityEnum::PUB,
        ), $constants);
    }

    public function testConstVisibilityExtended(): void
    {
        $constants = ConstVisibilityEnumExtended::getConstants();
        $this->assertSame(array(
            'IPUB'  => ConstVisibilityEnumExtended::IPUB,
            'PUB'   => ConstVisibilityEnumExtended::PUB,
            'IPUB2' => ConstVisibilityEnumExtended::IPUB2,
            'PUB2'  => ConstVisibilityEnumExtended::PUB2,
        ), $constants);
    }

    public function testIsSerializableIssue(): void
    {
        $enum1 = SerializableEnum::INT();

        /** @var SerializableEnum $enum2 */
        $enum2 = unserialize(serialize($enum1));

        $this->assertFalse($enum1 === $enum2, 'Wrong test implementation');
        $this->assertTrue($enum1->is($enum2), 'Two different instances of exact the same enumerator should be equal');
    }
}
