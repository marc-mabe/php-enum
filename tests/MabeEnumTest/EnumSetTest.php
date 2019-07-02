<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\EmptyEnum;
use MabeEnumTest\TestAsset\Enum31;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\Enum32;
use MabeEnumTest\TestAsset\Enum64;
use MabeEnumTest\TestAsset\Enum65;
use MabeEnumTest\TestAsset\Enum66;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the class MabeEnum\EnumSet
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumSetTest extends TestCase
{
    public function testBasicImmutable()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame(EnumBasic::class, $set->getEnumeration());

        $enum1 = EnumBasic::ONE();
        $enum2 = EnumBasic::TWO();

        $this->assertFalse($set->has($enum1));

        $set = $set->with($enum1);
        $this->assertTrue($set->has($enum1));
        $this->assertFalse($set->has($enum2));

        $set = $set->with($enum2);
        $this->assertTrue($set->has($enum2));

        $set = $set->without($enum1);
        $this->assertFalse($set->has($enum1));

        $set = $set->without($enum2);
        $this->assertFalse($set->has($enum2));
    }

    public function testBasicMutable()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame(EnumBasic::class, $set->getEnumeration());

        $enum1 = EnumBasic::ONE();
        $enum2 = EnumBasic::TWO();

        $this->assertFalse($set->has($enum1));

        $set->add($enum1);
        $this->assertTrue($set->has($enum1));
        $this->assertFalse($set->has($enum2));

        $set->add($enum2);
        $this->assertTrue($set->has($enum2));

        $set->remove($enum1);
        $this->assertFalse($set->has($enum1));

        $set->remove($enum2);
        $this->assertFalse($set->has($enum2));
    }

    public function testBasicMutableWithConstantValuesAsEnums()
    {
        $set = new EnumSet(EnumBasic::class);

        $enum1 = EnumBasic::ONE;
        $enum2 = EnumBasic::TWO;

        $this->assertFalse($set->has($enum1));

        $set->add($enum1);
        $this->assertTrue($set->has($enum1));
        $this->assertFalse($set->has($enum2));

        $set->add($enum2);
        $this->assertTrue($set->has($enum2));

        $set->remove($enum1);
        $this->assertFalse($set->has($enum1));

        $set->remove($enum2);
        $this->assertFalse($set->has($enum2));
    }

    public function testBasicImmutableWithConstantValuesAsEnums()
    {
        $set = new EnumSet(EnumBasic::class);

        $enum1 = EnumBasic::ONE;
        $enum2 = EnumBasic::TWO;

        $this->assertFalse($set->has($enum1));

        $set = $set->with($enum1);
        $this->assertTrue($set->has($enum1));
        $this->assertFalse($set->has($enum2));

        $set = $set->with($enum2);
        $this->assertTrue($set->has($enum2));

        $set = $set->without($enum1);
        $this->assertFalse($set->has($enum1));

        $set = $set->without($enum2);
        $this->assertFalse($set->has($enum2));
    }

    public function testInitArrayEnumerators()
    {
        $set = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO()]);

        $this->assertTrue($set->has(EnumBasic::ONE));
        $this->assertTrue($set->has(EnumBasic::TWO));
        $this->assertFalse($set->has(EnumBasic::THREE));
    }

    public function testInitInterableEnumerators()
    {
        $generator = (function() {
            yield EnumBasic::ONE();
            yield EnumBasic::TWO;
        })();

        $set = new EnumSet(EnumBasic::class, $generator);

        $this->assertTrue($set->has(EnumBasic::ONE));
        $this->assertTrue($set->has(EnumBasic::TWO));
        $this->assertFalse($set->has(EnumBasic::THREE));
    }

    public function testUnique()
    {
        $set = new EnumSet(EnumBasic::class);

        $set = $set->with(EnumBasic::ONE());
        $set = $set->with(EnumBasic::ONE);

        $set = $set->with(EnumBasic::TWO());
        $set = $set->with(EnumBasic::TWO);

        $this->assertSame(2, $set->count());
    }

    public function testConstructThrowsInvalidArgumentExceptionIfEnumClassDoesNotExtendBaseEnum()
    {
        $this->expectException(InvalidArgumentException::class);
        new EnumSet(self::class);
    }

    public function testInitEnumThrowsInvalidArgumentExceptionOnInvalidEnum()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->expectException(InvalidArgumentException::class);
        $this->assertFalse($set->has(EnumInheritance::INHERITANCE()));
    }

    /**
     * @param string $enumeration
     * @dataProvider getIntegerEnumerations
     */
    public function testAddRemove(string $enumeration)
    {
        $set = new EnumSet($enumeration);

        // attach all enumerators
        foreach ($enumeration::getConstants() as $value) {
            $this->assertFalse($set->has($value));
            $set->add($value);
            $this->assertTrue($set->has($value));
        }

        // check enumerator count
        $this->assertSame(count($enumeration::getConstants()), $set->count());

        // datch all enumerators
        foreach ($enumeration::getConstants() as $value) {
            $this->assertTrue($set->has($value));
            $set->remove($value);
            $this->assertFalse($set->has($value));
        }

        $this->assertSame(0, $set->count());
    }

    /**
     * @param string $enumeration
     * @dataProvider getIntegerEnumerations
     */
    public function testAddRemoveIterable(string $enumeration)
    {
        $set = new EnumSet($enumeration);

        // attach all enumerators at once
        $set->addIterable($enumeration::getConstants());
        $this->assertSame(count($enumeration::getConstants()), $set->count());

        // detach all enumerators at once
        $set->removeIterable($enumeration::getConstants());
        $this->assertSame(0, $set->count());
    }

    /**
     * @param string $enumeration
     * @dataProvider getIntegerEnumerations
     */
    public function testWithWithout(string $enumeration)
    {
        $set = new EnumSet($enumeration);

        // with all enumerators
        foreach ($enumeration::getConstants() as $value) {
            $this->assertFalse($set->has($value));
            $set = $set->with($value);
            $this->assertTrue($set->has($value));
        }

        $this->assertSame(count($enumeration::getConstants()), $set->count());

        // without all enumerators
        foreach ($enumeration::getConstants() as $value) {
            $this->assertTrue($set->has($value));
            $set = $set->without($value);
            $this->assertFalse($set->has($value));
        }

        $this->assertSame(0, $set->count());
    }

    /**
     * @param string $enumeration
     * @dataProvider getIntegerEnumerations
     */
    public function testWithWithoutIterable(string $enumeration)
    {
        $set = new EnumSet($enumeration);

        // with all enumerators at once
        $set = $set->withIterable($enumeration::getConstants());
        $this->assertSame(count($enumeration::getConstants()), $set->count());

        // without all enumerators at once
        $set = $set->withoutIterable($enumeration::getConstants());
        $this->assertSame(0, $set->count());
    }

    /**
     * Data provider for all available integer enumerators
     * @return array
     */
    public function getIntegerEnumerations()
    {
        return [
            [Enum31::class],
            [Enum32::class],
            [Enum64::class],
            [Enum65::class],
            [Enum66::class]
        ];
    }

    public function testAddIterableAtomic()
    {
        $set = new EnumSet(EnumBasic::class, [EnumBasic::ONE]);

        try {
            $set->addIterable([EnumBasic::TWO, 'unknown']);
        } catch (InvalidArgumentException $e) {
            // exception expected
        } finally {
            $this->assertTrue($set->has(EnumBasic::ONE));
            $this->assertFalse($set->has(EnumBasic::TWO));
        }
    }

    public function testRemoveIterableAtomic()
    {
        $set = new EnumSet(EnumBasic::class, [EnumBasic::ONE]);

        try {
            $set->removeIterable([EnumBasic::ONE, EnumBasic::TWO, 'unknown']);
        } catch (InvalidArgumentException $e) {
            // exception expected
        } finally {
            $this->assertTrue($set->has(EnumBasic::ONE));
            $this->assertFalse($set->has(EnumBasic::TWO));
        }
    }

    public function testGetBit()
    {
        $set = new EnumSet(EnumBasic::class, [EnumBasic::TWO]);

        $this->assertFalse($set->getBit(EnumBasic::ONE()->getOrdinal()));
        $this->assertTrue($set->getBit(EnumBasic::TWO()->getOrdinal()));
    }

    public function testGetBitOutOfRange()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $set->getBit(100);
    }

    public function testSetBit()
    {
        $set = new EnumSet(EnumBasic::class);

        $set->setBit(EnumBasic::TWO()->getOrdinal(), true);
        $this->assertTrue($set->getBit(EnumBasic::TWO()->getOrdinal()));

        $set->setBit(EnumBasic::TWO()->getOrdinal(), false);
        $this->assertFalse($set->getBit(EnumBasic::TWO()->getOrdinal()));
    }

    public function testWithBit()
    {
        $set = new EnumSet(EnumBasic::class);

        $set = $set->withBit(EnumBasic::TWO()->getOrdinal(), true);
        $this->assertTrue($set->getBit(EnumBasic::TWO()->getOrdinal()));

        $set = $set->withBit(EnumBasic::TWO()->getOrdinal(), false);
        $this->assertFalse($set->getBit(EnumBasic::TWO()->getOrdinal()));
    }

    public function testSetBitOutOfRange()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $set->setBit(100, true);
    }

    public function testGetBinaryBitsetLe()
    {
        $set = new EnumSet(Enum65::class);

        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $set = $set->with($enum1);
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x00", $set->getBinaryBitsetLe());
        $this->assertTrue($set->has($enum1));

        $set = $set->with($enum2);
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x00\x00", $set->getBinaryBitsetLe());
        $this->assertTrue($set->has($enum2));

        $set = $set->with($enum3);
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x00\x01", $set->getBinaryBitsetLe());
        $this->assertTrue($set->has($enum3));

        $set = $set->with($enum4);
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x80\x01", $set->getBinaryBitsetLe());
        $this->assertTrue($set->has($enum4));

        $this->assertSame(4, $set->count());

        $set = $set->without($enum2);
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x80\x01", $set->getBinaryBitsetLe());
        $this->assertFalse($set->has($enum2));

        $this->assertSame(3, $set->count());
    }

    public function testGetBinaryBitsetBe()
    {
        $set = new EnumSet(Enum65::class);

        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $set = $set->with($enum1);
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x01", $set->getBinaryBitsetBe());
        $this->assertTrue($set->has($enum1));

        $set = $set->with($enum2);
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x03", $set->getBinaryBitsetBe());
        $this->assertTrue($set->has($enum2));

        $set = $set->with($enum3);
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x03", $set->getBinaryBitsetBe());
        $this->assertTrue($set->has($enum3));

        $set = $set->with($enum4);
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x03", $set->getBinaryBitsetBe());
        $this->assertTrue($set->has($enum4));

        $this->assertSame(4, $set->count());

        $set = $set->without($enum2);
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x01", $set->getBinaryBitsetBe());
        $this->assertFalse($set->has($enum2));

        $this->assertSame(3, $set->count());
    }

    public function testSetBinaryBitsetLeBin()
    {
        $set = new EnumSet(Enum65::class);
        $set->setBinaryBitsetLe("\x01\x00\x00\x00\x00\x00\x00\x80\x01");

        $this->assertContains(Enum65::ONE(), $set);
        $this->assertNotContains(Enum65::TWO(), $set);
        $this->assertContains(Enum65::SIXTYFIVE(), $set);
        $this->assertContains(Enum65::SIXTYFOUR(), $set);
        $this->assertSame(3, $set->count());
    }

    public function testWithBinaryBitsetLeBin()
    {
        $set = new EnumSet(Enum65::class);
        $set = $set->withBinaryBitsetLe("\x01\x00\x00\x00\x00\x00\x00\x80\x01");

        $this->assertContains(Enum65::ONE(), $set);
        $this->assertNotContains(Enum65::TWO(), $set);
        $this->assertContains(Enum65::SIXTYFIVE(), $set);
        $this->assertContains(Enum65::SIXTYFOUR(), $set);
        $this->assertSame(3, $set->count());
    }

    public function testWithBinaryBitsetLeBinShort()
    {
        $set = new EnumSet(Enum65::class);
        $set = $set->withBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00\x00\x00\x00\x00\x00", $set->getBinaryBitsetLe());
    }

    public function testWithBinaryBitsetLeBinLong()
    {
        $set = new EnumSet(Enum65::class);
        $bitset = "\x0A\xFF\x00\x00\x00\x00\x00\x00\x00";
        $set = $set->withBinaryBitsetLe($bitset . "\x00\x00\x00\x00\x00\x00\x00");
        $this->assertSame($bitset, $set->getBinaryBitsetLe());
    }

    public function testWithBinaryBitsetLeBinOutOfRangeBitsOfExtendedBytes1()
    {
        $set = new EnumSet(Enum65::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->withBinaryBitsetLe("\xff\xff\xff\xff\xff\xff\xff\xff\x00\x02");
    }

    public function testWithBinaryBitsetLeBinOutOfRangeBitsOfExtendedBytes2()
    {
        $set = new EnumSet(Enum65::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->withBinaryBitsetLe("\xff\xff\xff\xff\xff\xff\xff\xff\x02");
    }

    public function testWithBinaryBitsetLeBinOutOfRangeBitsOfLastValidByte()
    {
        // using Enum65 to detect Out-Of-Range bits of last valid byte
        // Enum65 has max. ordinal number of 2 of the last byte. -> 0001
        $set   = new EnumSet(Enum65::class);
        $bitset    = $set->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\x02";

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->withBinaryBitsetLe($newBitset);
    }

    public function testWithBinaryBitsetLeInt()
    {
        $set = new EnumSet(Enum32::class);
        $set = $set->withBinaryBitsetLe("\x01\x00\x80\x01");
        $this->assertContains(Enum32::ONE(), $set);
        $this->assertNotContains(Enum32::TWO(), $set);
        $this->assertContains(Enum32::TWENTYFOUR(), $set);
        $this->assertContains(Enum32::TWENTYFIVE(), $set);
        $this->assertSame(3, $set->count());
    }

    public function testWithBinaryBitsetLeIntShort()
    {
        $set = new EnumSet(Enum32::class);
        $set = $set->withBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00", $set->getBinaryBitsetLe());
    }

    public function testWithBinaryBitsetLeIntOutOfRangeBitsOfExtendedBytes1()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->withBinaryBitsetLe("\x0A\xFF\x02");
    }

    public function testWithBinaryBitsetLeIntOutOfRangeBitsOfExtendedBytes2()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->withBinaryBitsetLe("\x01\x01\x01\x01\x01\x01\x01\x01\x01");
    }

    public function testWithBinaryBitsetLeIntOutOfRangeBitsOfLastValidByte()
    {
        // using Enum65 to detect Out-Of-Range bits of last valid byte
        // Enum65 has max. ordinal number of 2 of the last byte. -> 0001
        $set   = new EnumSet(Enum31::class);
        $bitset    = $set->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\xFF";

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->withBinaryBitsetLe($newBitset);
    }

    public function testSetBinaryBitsetBe()
    {
        $set = new EnumSet(Enum65::class);
        $set->setBinaryBitsetBe("\x01\x80\x00\x00\x00\x00\x00\x00\x01");

        $this->assertTrue($set->has(Enum65::ONE));
        $this->assertFalse($set->has(Enum65::TWO));
        $this->assertTrue($set->has(Enum65::SIXTYFIVE));
        $this->assertTrue($set->has(Enum65::SIXTYFOUR));
        $this->assertTrue($set->count() == 3);
    }

    public function testWithBinaryBitsetBe()
    {
        $set = new EnumSet(Enum65::class);
        $set = $set->withBinaryBitsetBe("\x01\x80\x00\x00\x00\x00\x00\x00\x01");

        $this->assertTrue($set->has(Enum65::ONE));
        $this->assertFalse($set->has(Enum65::TWO));
        $this->assertTrue($set->has(Enum65::SIXTYFIVE));
        $this->assertTrue($set->has(Enum65::SIXTYFOUR));
        $this->assertTrue($set->count() == 3);
    }

    public function testCountingEmptyEnumEmptySet()
    {
        $set = new EnumSet(EmptyEnum::class);
        $this->assertSame(0, $set->count());
    }

    public function testCountSingleBit32()
    {
        foreach (Enum32::getEnumerators() as $enum) {
            $set = new EnumSet(Enum32::class);
            $set = $set->with($enum);
            $this->assertSame(1, $set->count());
        }
    }

    public function testCountSingleBit64()
    {
        foreach (Enum64::getEnumerators() as $enum) {
            $set = new EnumSet(Enum64::class);
            $set = $set->with($enum);
            $this->assertSame(1, $set->count());
        }
    }

    public function testCountSingleBit66()
    {
        foreach (Enum66::getEnumerators() as $enum) {
            $set = new EnumSet(Enum66::class);
            $set = $set->with($enum);
            $this->assertSame(1, $set->count());
        }
    }

    public function testIsEqual()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumBasic::class);
        $this->assertTrue($set1->isEqual($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $this->assertFalse($set1->isEqual($set2));

            $set2 = $set2->with($enumerator);
            $this->assertTrue($set1->isEqual($set2));
        }
    }

    public function testIsEqualWrongInstance()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumInheritance::class);
        $this->assertFalse($set1->isEqual($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $this->assertFalse($set1->isEqual($set2));

            $set2 = $set2->with($enumerator->getValue());
            $this->assertFalse($set1->isEqual($set2));
        }
    }

    /**
     * if $A->isEqual($B) is true then $A->isSubsetOf($B) is also true
     */
    public function testIsSubsetIsEqual()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);
        $this->assertTrue($set1->isSubset($set2));

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $set2 = $set2->with($enumerator);
            $this->assertTrue($set1->isSubset($set2));
        }
    }

    public function testIsSubsetFull()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set2 = $set2->with($enumerator);
            $this->assertTrue($set1->isSubset($set2));
        }
    }

    public function testIsSubsetFalse()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $this->assertFalse($set1->isSubset($set2));
        }
    }

    public function testIsSubsetWrongInstance()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumInheritance::class);
        $this->assertFalse($set1->isSubset($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $this->assertFalse($set1->isSubset($set2));

            $set2 = $set2->with($enumerator->getValue());
            $this->assertFalse($set1->isSubset($set2));
        }
    }

    /**
     * if $A->isEqual($B) is true then $A->isSuperset($B) is also true
     */
    public function testIsSupersetIsEqual()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);
        $this->assertTrue($set1->isEqual($set2));
        $this->assertTrue($set1->isSuperset($set2));

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $set2 = $set2->with($enumerator);
            $this->assertTrue($set1->isEqual($set2));
            $this->assertTrue($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetFull()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $this->assertTrue($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetFalse()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set2 = $set2->with($enumerator);
            $this->assertFalse($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetWrongInstance()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumInheritance::class);
        $this->assertFalse($set1->isSuperset($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1 = $set1->with($enumerator);
            $this->assertFalse($set1->isSuperset($set2));

            $set2 = $set2->with($enumerator->getValue());
            $this->assertFalse($set1->isSuperset($set2));
        }
    }

    /**
     * @dataProvider getIntegerEnumerations
     */
    public function testIsEmpty($enum)
    {
        $set1 = new EnumSet($enum, []);
        $set2 = new EnumSet($enum, $enum::getValues());

        $this->assertTrue($set1->isEmpty());
        $this->assertFalse($set2->isEmpty());

        $set1->addIterable($enum::getValues());
        $set2->removeIterable($enum::getValues());

        $this->assertFalse($set1->isEmpty());
        $this->assertTrue($set2->isEmpty());
    }

    public function testGetOrdinalsInt()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getOrdinals());

        foreach (EnumBasic::getConstants() as $value) {
            $set = $set->with($value);
        }

        $this->assertSame(range(0, count(EnumBasic::getConstants()) - 1), $set->getOrdinals());
    }

    public function testGetOrdinalsBin()
    {
        $set = new EnumSet(Enum66::class);
        $this->assertSame([], $set->getOrdinals());

        foreach (Enum66::getConstants() as $value) {
            $set = $set->with($value);
        }

        $this->assertSame(range(0, count(Enum66::getConstants()) - 1), $set->getOrdinals());
    }

    public function testGetEnumerators()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getEnumerators());

        foreach (EnumBasic::getConstants() as $value) {
            $set = $set->with($value);
        }

        $this->assertSame(EnumBasic::getEnumerators(), $set->getEnumerators());
    }

    public function testGetValues()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getValues());

        foreach (EnumBasic::getConstants() as $value) {
            $set = $set->with($value);
        }

        $this->assertSame(array_values(EnumBasic::getConstants()), $set->getValues());
    }

    public function testGetNames()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getNames());

        foreach (EnumBasic::getConstants() as $value) {
            $set = $set->with($value);
        }

        $this->assertSame(array_keys(EnumBasic::getConstants()), $set->getNames());
    }

    public function testSetUnion()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $set1->setUnion($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::TWO,
            EnumBasic::THREE,
            EnumBasic::FOUR,
        ], $set1->getValues());
    }

    public function testWithUnion()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->withUnion($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::TWO,
            EnumBasic::THREE,
            EnumBasic::FOUR,
        ], $rs->getValues());
    }

    public function testSetUnionThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->setUnion($set2);
    }

    public function testSetIntersect()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $set1->setIntersect($set2);
        $this->assertSame([EnumBasic::TWO, EnumBasic::THREE], $set1->getValues());
    }

    public function testWithIntersect()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->withIntersect($set2);
        $this->assertSame([EnumBasic::TWO, EnumBasic::THREE], $rs->getValues());
    }

    public function testSetIntersectThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->setIntersect($set2);
    }

    public function testSetDiff()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $set1->setDiff($set2);
        $this->assertSame([EnumBasic::ONE], $set1->getValues());
    }

    public function testWithDiff()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->withDiff($set2);
        $this->assertSame([EnumBasic::ONE], $rs->getValues());
    }

    public function testSetDiffThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->setDiff($set2);
    }

    public function testSetSymDiff()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $set1->setSymDiff($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::FOUR,
        ], $set1->getValues());
    }

    public function testWithSymDiff()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->withSymDiff($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::FOUR,
        ], $rs->getValues());
    }

    public function testSetSymDiffThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->setSymDiff($set2);
    }

    /* deprecated */

    /** @deprecated */
    public function testUnion()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->union($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::TWO,
            EnumBasic::THREE,
            EnumBasic::FOUR,
        ], $rs->getValues());
    }

    /** @deprecated */
    public function testIntersect()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->intersect($set2);
        $this->assertSame([EnumBasic::TWO, EnumBasic::THREE], $rs->getValues());
    }

    /** @deprecated */
    public function testDiff()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->diff($set2);
        $this->assertSame([EnumBasic::ONE], $rs->getValues());
    }

    /** @deprecated */
    public function testSymDiff()
    {
        $set1 = new EnumSet(EnumBasic::class, [EnumBasic::ONE, EnumBasic::TWO, EnumBasic::THREE]);
        $set2 = new EnumSet(EnumBasic::class, [EnumBasic::TWO, EnumBasic::THREE, EnumBasic::FOUR]);

        $rs = $set1->symDiff($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::FOUR,
        ], $rs->getValues());
    }

    /** @deprecated */
    public function testAttachDetach()
    {
        $set = new EnumSet(EnumBasic::class);

        $set->attach(EnumBasic::ONE);
        $this->assertSame([EnumBasic::ONE()], $set->getEnumerators());

        $set->detach(EnumBasic::ONE);
        $this->assertSame([], $set->getEnumerators());
    }

    /** @deprecated */
    public function testContains()
    {
        $set = new EnumSet(EnumBasic::class, [EnumBasic::ONE]);

        $this->assertTrue($set->contains(EnumBasic::ONE));
        $this->assertFalse($set->contains(EnumBasic::TWO));
    }
}
