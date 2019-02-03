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
    public function testBasic()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame(EnumBasic::class, $set->getEnumeration());

        $enum1  = EnumBasic::ONE();
        $enum2  = EnumBasic::TWO();

        $this->assertFalse($set->contains($enum1));
        $this->assertNull($set->attach($enum1));
        $this->assertTrue($set->contains($enum1));

        $this->assertFalse($set->contains($enum2));
        $this->assertNull($set->attach($enum2));
        $this->assertTrue($set->contains($enum2));

        $this->assertNull($set->detach($enum1));
        $this->assertFalse($set->contains($enum1));

        $this->assertNull($set->detach($enum2));
        $this->assertFalse($set->contains($enum2));
    }

    public function testBasicWithConstantValuesAsEnums()
    {
        $set = new EnumSet(EnumBasic::class);

        $enum1  = EnumBasic::ONE;
        $enum2  = EnumBasic::TWO;

        $this->assertFalse($set->contains($enum1));
        $this->assertNull($set->attach($enum1));
        $this->assertTrue($set->contains($enum1));

        $this->assertFalse($set->contains($enum2));
        $this->assertNull($set->attach($enum2));
        $this->assertTrue($set->contains($enum2));

        $this->assertNull($set->detach($enum1));
        $this->assertFalse($set->contains($enum1));

        $this->assertNull($set->detach($enum2));
        $this->assertFalse($set->contains($enum2));
    }

    public function testUnique()
    {
        $set = new EnumSet(EnumBasic::class);

        $set->attach(EnumBasic::ONE());
        $set->attach(EnumBasic::ONE);

        $set->attach(EnumBasic::TWO());
        $set->attach(EnumBasic::TWO);

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
        $this->assertFalse($set->contains(EnumInheritance::INHERITANCE()));
    }

    /**
     * @param string $enumeration
     * @dataProvider getIntegerEnumerations
     */
    public function testSetAllEnumerators(string $enumeration)
    {
        $set = new EnumSet($enumeration);
        foreach ($enumeration::getConstants() as $value) {
            $this->assertFalse($set->contains($value));
            $set->attach($value);
            $this->assertTrue($set->contains($value));
        }

        $this->assertSame(count($enumeration::getConstants()), $set->count());

        $expectedOrdinal = 0;
        foreach ($set as $ordinal => $enum) {
            $this->assertSame($expectedOrdinal, $ordinal);
            $this->assertSame($expectedOrdinal, $enum->getOrdinal());
            $expectedOrdinal++;
        }
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

    public function testGetBit()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->attach(EnumBasic::TWO);

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

        $this->assertNull($set->attach($enum1));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x00", $set->getBinaryBitsetLe());
        $this->assertTrue($set->contains($enum1));

        $this->assertNull($set->attach($enum2));
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x00\x00", $set->getBinaryBitsetLe());
        $this->assertTrue($set->contains($enum2));

        $this->assertNull($set->attach($enum3));
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x00\x01", $set->getBinaryBitsetLe());
        $this->assertTrue($set->contains($enum3));

        $this->assertNull($set->attach($enum4));
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x80\x01", $set->getBinaryBitsetLe());
        $this->assertTrue($set->contains($enum4));
        
        $this->assertSame(4, $set->count());

        $this->assertNull($set->detach($enum2));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x80\x01", $set->getBinaryBitsetLe());
        $this->assertFalse($set->contains($enum2));
        
        $this->assertSame(3, $set->count());
    }

    public function testGetBinaryBitsetBe()
    {
        $set = new EnumSet(Enum65::class);
        
        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $this->assertNull($set->attach($enum1));
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x01", $set->getBinaryBitsetBe());
        $this->assertTrue($set->contains($enum1));

        $this->assertNull($set->attach($enum2));
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x03", $set->getBinaryBitsetBe());
        $this->assertTrue($set->contains($enum2));

        $this->assertNull($set->attach($enum3));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x03", $set->getBinaryBitsetBe());
        $this->assertTrue($set->contains($enum3));

        $this->assertNull($set->attach($enum4));
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x03", $set->getBinaryBitsetBe());
        $this->assertTrue($set->contains($enum4));
        
        $this->assertSame(4, $set->count());

        $this->assertNull($set->detach($enum2));
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x01", $set->getBinaryBitsetBe());
        $this->assertFalse($set->contains($enum2));
        
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

    public function testSetBinaryBitsetLeBinShort()
    {
        $set = new EnumSet(Enum65::class);
        $set->setBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00\x00\x00\x00\x00\x00", $set->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeBinLong()
    {
        $set = new EnumSet(Enum65::class);
        $bitset = "\x0A\xFF\x00\x00\x00\x00\x00\x00\x00";
        $set->setBinaryBitsetLe($bitset . "\x00\x00\x00\x00\x00\x00\x00");
        $this->assertSame($bitset, $set->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeBinOutOfRangeBitsOfExtendedBytes1()
    {
        $set = new EnumSet(Enum65::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->setBinaryBitsetLe("\xff\xff\xff\xff\xff\xff\xff\xff\x00\x02");
    }

    public function testSetBinaryBitsetLeBinOutOfRangeBitsOfExtendedBytes2()
    {
        $set = new EnumSet(Enum65::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->setBinaryBitsetLe("\xff\xff\xff\xff\xff\xff\xff\xff\x02");
    }

    public function testSetBinaryBitsetLeBinOutOfRangeBitsOfLastValidByte()
    {
        // using Enum65 to detect Out-Of-Range bits of last valid byte
        // Enum65 has max. ordinal number of 2 of the last byte. -> 0001
        $set   = new EnumSet(Enum65::class);
        $bitset    = $set->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\x02";

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->setBinaryBitsetLe($newBitset);
    }

    public function testSetBinaryBitsetLeInt()
    {
        $set = new EnumSet(Enum32::class);
        $set->setBinaryBitsetLe("\x01\x00\x80\x01");
        $this->assertContains(Enum32::ONE(), $set);
        $this->assertNotContains(Enum32::TWO(), $set);
        $this->assertContains(Enum32::TWENTYFOUR(), $set);
        $this->assertContains(Enum32::TWENTYFIVE(), $set);
        $this->assertSame(3, $set->count());
    }

    public function testSetBinaryBitsetLeIntShort()
    {
        $set = new EnumSet(Enum32::class);
        $set->setBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00", $set->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeIntOutOfRangeBitsOfExtendedBytes1()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->setBinaryBitsetLe("\x0A\xFF\x02");
    }

    public function testSetBinaryBitsetLeIntOutOfRangeBitsOfExtendedBytes2()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->setBinaryBitsetLe("\x01\x01\x01\x01\x01\x01\x01\x01\x01");
    }

    public function testSetBinaryBitsetLeIntOutOfRangeBitsOfLastValidByte()
    {
        // using Enum65 to detect Out-Of-Range bits of last valid byte
        // Enum65 has max. ordinal number of 2 of the last byte. -> 0001
        $set   = new EnumSet(Enum31::class);
        $bitset    = $set->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\xFF";

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $set->setBinaryBitsetLe($newBitset);
    }

    public function testSetBinaryBitsetBe()
    {
        $set = new EnumSet(Enum65::class);
        $set->setBinaryBitsetBe("\x01\x80\x00\x00\x00\x00\x00\x00\x01");

        $this->assertTrue($set->contains(Enum65::ONE));
        $this->assertFalse($set->contains(Enum65::TWO));
        $this->assertTrue($set->contains(Enum65::SIXTYFIVE));
        $this->assertTrue($set->contains(Enum65::SIXTYFOUR));
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
            $set->attach($enum);
            $this->assertSame(1, $set->count());
        }
    }

    public function testCountSingleBit64()
    {
        foreach (Enum64::getEnumerators() as $enum) {
            $set = new EnumSet(Enum64::class);
            $set->attach($enum);
            $this->assertSame(1, $set->count());
        }
    }

    public function testCountSingleBit66()
    {
        foreach (Enum66::getEnumerators() as $enum) {
            $set = new EnumSet(Enum66::class);
            $set->attach($enum);
            $this->assertSame(1, $set->count());
        }
    }

    public function testIsEqual()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumBasic::class);
        $this->assertTrue($set1->isEqual($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isEqual($set2));

            $set2->attach($enumerator);
            $this->assertTrue($set1->isEqual($set2));
        }
    }

    public function testIsEqualWrongInstance()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumInheritance::class);
        $this->assertFalse($set1->isEqual($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isEqual($set2));

            $set2->attach($enumerator->getValue());
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
            $set1->attach($enumerator);
            $set2->attach($enumerator);
            $this->assertTrue($set1->isSubset($set2));
        }
    }

    public function testIsSubsetFull()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set2->attach($enumerator);
            $this->assertTrue($set1->isSubset($set2));
        }
    }

    public function testIsSubsetFalse()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isSubset($set2));
        }
    }

    public function testIsSubsetWrongInstance()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumInheritance::class);
        $this->assertFalse($set1->isSubset($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isSubset($set2));

            $set2->attach($enumerator->getValue());
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
            $set1->attach($enumerator);
            $set2->attach($enumerator);
            $this->assertTrue($set1->isEqual($set2));
            $this->assertTrue($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetFull()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertTrue($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetFalse()
    {
        $set1 = new EnumSet(Enum32::class);
        $set2 = new EnumSet(Enum32::class);

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set2->attach($enumerator);
            $this->assertFalse($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetWrongInstance()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(EnumInheritance::class);
        $this->assertFalse($set1->isSuperset($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isSuperset($set2));

            $set2->attach($enumerator->getValue());
            $this->assertFalse($set1->isSuperset($set2));
        }
    }

    public function testGetOrdinalsInt()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getOrdinals());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(range(0, count(EnumBasic::getConstants()) - 1), $set->getOrdinals());
    }

    public function testGetOrdinalsBin()
    {
        $set = new EnumSet(Enum66::class);
        $this->assertSame([], $set->getOrdinals());

        foreach (Enum66::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(range(0, count(Enum66::getConstants()) - 1), $set->getOrdinals());
    }

    public function testGetEnumerators()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getEnumerators());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(EnumBasic::getEnumerators(), $set->getEnumerators());
    }

    public function testGetValues()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getValues());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(array_values(EnumBasic::getConstants()), $set->getValues());
    }

    public function testGetNames()
    {
        $set = new EnumSet(EnumBasic::class);
        $this->assertSame([], $set->getNames());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(array_keys(EnumBasic::getConstants()), $set->getNames());
    }

    public function testUnion()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);

        $set2 = new EnumSet(EnumBasic::class);
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $rs = $set1->union($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::TWO,
            EnumBasic::THREE,
            EnumBasic::FOUR,
        ], $rs->getValues());
    }

    public function testUnionThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->union($set2);
    }

    public function testIntersect()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);
        $set1->attach(EnumBasic::THREE);

        $set2 = new EnumSet(EnumBasic::class);
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $rs = $set1->intersect($set2);
        $this->assertSame([EnumBasic::TWO, EnumBasic::THREE], $rs->getValues());
    }

    public function testIntersectThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->intersect($set2);
    }

    public function testDiff()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);
        $set1->attach(EnumBasic::THREE);

        $set2 = new EnumSet(EnumBasic::class);
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $rs = $set1->diff($set2);
        $this->assertSame([EnumBasic::ONE], $rs->getValues());
    }

    public function testDiffThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->diff($set2);
    }

    public function testSymDiff()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);
        $set1->attach(EnumBasic::THREE);

        $set2 = new EnumSet(EnumBasic::class);
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $rs = $set1->symDiff($set2);
        $this->assertSame([
            EnumBasic::ONE,
            EnumBasic::FOUR,
        ], $rs->getValues());
    }

    public function testSymDiffThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet(EnumBasic::class);
        $set2 = new EnumSet(Enum32::class);

        $this->expectException(InvalidArgumentException::class);
        $set1->symDiff($set2);
    }
}
