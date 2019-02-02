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
        $enumSet = new EnumSet(EnumBasic::class);
        $this->assertSame(EnumBasic::class, $enumSet->getEnumeration());

        $enum1  = EnumBasic::ONE();
        $enum2  = EnumBasic::TWO();

        $this->assertFalse($enumSet->contains($enum1));
        $this->assertNull($enumSet->attach($enum1));
        $this->assertTrue($enumSet->contains($enum1));

        $this->assertFalse($enumSet->contains($enum2));
        $this->assertNull($enumSet->attach($enum2));
        $this->assertTrue($enumSet->contains($enum2));

        $this->assertNull($enumSet->detach($enum1));
        $this->assertFalse($enumSet->contains($enum1));

        $this->assertNull($enumSet->detach($enum2));
        $this->assertFalse($enumSet->contains($enum2));
    }

    public function testBasicWithConstantValuesAsEnums()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $enum1  = EnumBasic::ONE;
        $enum2  = EnumBasic::TWO;

        $this->assertFalse($enumSet->contains($enum1));
        $this->assertNull($enumSet->attach($enum1));
        $this->assertTrue($enumSet->contains($enum1));

        $this->assertFalse($enumSet->contains($enum2));
        $this->assertNull($enumSet->attach($enum2));
        $this->assertTrue($enumSet->contains($enum2));

        $this->assertNull($enumSet->detach($enum1));
        $this->assertFalse($enumSet->contains($enum1));

        $this->assertNull($enumSet->detach($enum2));
        $this->assertFalse($enumSet->contains($enum2));
    }

    public function testUnique()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $enumSet->attach(EnumBasic::ONE());
        $enumSet->attach(EnumBasic::ONE);

        $enumSet->attach(EnumBasic::TWO());
        $enumSet->attach(EnumBasic::TWO);

        $this->assertSame(2, $enumSet->count());
    }

    public function testIterateOrdered()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        // an empty enum set needs to be invalid, starting by 0
        $this->assertSame(0, $enumSet->count());
        $this->assertFalse($enumSet->valid());
        $this->assertNull($enumSet->current());

        // attach
        $enum1 = EnumBasic::ONE();
        $enum2 = EnumBasic::TWO();
        $enumSet->attach($enum1);
        $enumSet->attach($enum2);

        // a not empty enum set should be valid, starting by 0 (if not iterated)
        $enumSet->rewind();
        $this->assertSame(2, $enumSet->count());
        $this->assertTrue($enumSet->valid());
        $this->assertSame($enum1->getOrdinal(), $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());

        // go to the next element (last)
        $this->assertNull($enumSet->next());
        $this->assertTrue($enumSet->valid());
        $this->assertSame($enum2->getOrdinal(), $enumSet->key());
        $this->assertSame($enum2, $enumSet->current());

        // go to the next element (out of range)
        $this->assertNull($enumSet->next());
        $this->assertFalse($enumSet->valid());
        $this->assertNull($enumSet->current());

        // rewind will set the iterator position back to 0
        $enumSet->rewind();
        $this->assertTrue($enumSet->valid());
        $this->assertSame(0, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());
    }

    public function testIterateAndDetach()
    {
        $enumSet = new EnumSet(EnumInheritance::class);

        $enum1 = EnumInheritance::ONE();
        $enum2 = EnumInheritance::TWO();
        $enum3 = EnumInheritance::INHERITANCE();

        // attach
        $enumSet->attach($enum1);
        $enumSet->attach($enum2);
        $enumSet->attach($enum3);

        // go to the next entry
        $enumSet->next();
        $this->assertSame($enum2, $enumSet->current());

        // detach current entry
        $enumSet->detach($enumSet->current());
        $this->assertFalse($enumSet->valid());
        $this->assertNull($enumSet->current());
        $this->assertSame($enum2->getOrdinal(), $enumSet->key());

        // go to the next entry should be the last entry
        $enumSet->next();
        $this->assertSame($enum3, $enumSet->current());

        // detech the last entry
        $enumSet->detach($enumSet->current());
        $this->assertFalse($enumSet->valid());
        $this->assertNull($enumSet->current());
        $this->assertSame($enum3->getOrdinal(), $enumSet->key());
    }

    public function testConstructThrowsInvalidArgumentExceptionIfEnumClassDoesNotExtendBaseEnum()
    {
        $this->expectException(InvalidArgumentException::class);
        new EnumSet(self::class);
    }

    public function testInitEnumThrowsInvalidArgumentExceptionOnInvalidEnum()
    {
        $enumSet = new EnumSet(EnumBasic::class);
        $this->expectException(InvalidArgumentException::class);
        $this->assertFalse($enumSet->contains(EnumInheritance::INHERITANCE()));
    }

    public function testIterateOutOfRangeIfLastOrdinalEnumIsSet()
    {
        $enumSet = new EnumSet(EnumBasic::class);
        $enum    = EnumBasic::byOrdinal(count(EnumBasic::getConstants()) - 1);

        $enumSet->attach($enum);
        $enumSet->rewind();
        $this->assertSame($enum, $enumSet->current());

        // go to the next entry results in out of range
        $enumSet->next();
        $this->assertFalse($enumSet->valid());
        $this->assertSame($enum->getOrdinal() + 1, $enumSet->key());

        // go more over doesn't change iterator position
        $enumSet->next();
        $this->assertFalse($enumSet->valid());
        $this->assertSame($enum->getOrdinal() + 1, $enumSet->key());
    }

    public function testRewindIntFirstOnEmptySet()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $enumSet->attach(EnumBasic::TWO);
        $enumSet->rewind();
        $this->assertGreaterThan(0, $enumSet->key());

        $enumSet->detach(EnumBasic::TWO);
        $enumSet->rewind();
        $this->assertSame(0, $enumSet->key());
    }

    public function testRewindBinFirstOnEmptySet()
    {
        $enumSet = new EnumSet(Enum66::class);

        $enumSet->attach(Enum66::TWO);
        $enumSet->rewind();
        $this->assertGreaterThan(0, $enumSet->key());

        $enumSet->detach(Enum66::TWO);
        $enumSet->rewind();
        $this->assertSame(0, $enumSet->key());
    }

    public function test32EnumerationsSet()
    {
        $enumSet = new EnumSet(Enum32::class);
        foreach (Enum32::getConstants() as $name => $value) {
            $this->assertFalse($enumSet->contains($value));
            $enumSet->attach($value);
            $this->assertTrue($enumSet->contains($value));
        }

        $this->assertSame(32, $enumSet->count());

        $expectedOrdinal = 0;
        foreach ($enumSet as $ordinal => $enum) {
            $this->assertSame($expectedOrdinal, $ordinal);
            $this->assertSame($expectedOrdinal, $enum->getOrdinal());
            $expectedOrdinal++;
        }
    }

    public function test64EnumerationsSet()
    {
        $enumSet = new EnumSet(Enum64::class);
        foreach (Enum64::getConstants() as $name => $value) {
            $this->assertFalse($enumSet->contains($value));
            $enumSet->attach($value);
            $this->assertTrue($enumSet->contains($value));
        }

        $this->assertSame(64, $enumSet->count());

        $expectedOrdinal = 0;
        foreach ($enumSet as $ordinal => $enum) {
            $this->assertSame($expectedOrdinal, $ordinal);
            $this->assertSame($expectedOrdinal, $enum->getOrdinal());
            $expectedOrdinal++;
        }
    }

    public function test65EnumerationsSet()
    {
        $enum = new EnumSet(Enum65::class);

        $this->assertNull($enum->attach(Enum65::byOrdinal(64)));
        $enum->next();
        $this->assertTrue($enum->valid());
    }

    public function testGetBit()
    {
        $enumSet = new EnumSet(EnumBasic::class);
        $enumSet->attach(EnumBasic::TWO);

        $this->assertFalse($enumSet->getBit(EnumBasic::ONE()->getOrdinal()));
        $this->assertTrue($enumSet->getBit(EnumBasic::TWO()->getOrdinal()));
    }

    public function testGetBitOutOfRangeOrdinal()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $enumSet->getBit(100);
    }

    public function testSetBit()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $enumSet->setBit(EnumBasic::TWO()->getOrdinal(), true);
        $this->assertTrue($enumSet->getBit(EnumBasic::TWO()->getOrdinal()));

        $enumSet->setBit(EnumBasic::TWO()->getOrdinal(), false);
        $this->assertFalse($enumSet->getBit(EnumBasic::TWO()->getOrdinal()));
    }

    public function testSetBitOutOfRangeOrdinal()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $enumSet->setBit(100, true);
    }

    public function testGetBinaryBitsetLe()
    {
        $enumSet = new EnumSet(Enum65::class);
        
        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $this->assertNull($enumSet->attach($enum1));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x00", $enumSet->getBinaryBitsetLe());
        $this->assertTrue($enumSet->contains($enum1));

        $this->assertNull($enumSet->attach($enum2));
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x00\x00", $enumSet->getBinaryBitsetLe());
        $this->assertTrue($enumSet->contains($enum2));

        $this->assertNull($enumSet->attach($enum3));
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x00\x01", $enumSet->getBinaryBitsetLe());
        $this->assertTrue($enumSet->contains($enum3));

        $this->assertNull($enumSet->attach($enum4));
        $this->assertSame("\x03\x00\x00\x00\x00\x00\x00\x80\x01", $enumSet->getBinaryBitsetLe());
        $this->assertTrue($enumSet->contains($enum4));
        
        $this->assertSame(4, $enumSet->count());

        $this->assertNull($enumSet->detach($enum2));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x80\x01", $enumSet->getBinaryBitsetLe());
        $this->assertFalse($enumSet->contains($enum2));
        
        $this->assertSame(3, $enumSet->count());
    }

    public function testGetBinaryBitsetBe()
    {
        $enumSet = new EnumSet(Enum65::class);
        
        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $this->assertNull($enumSet->attach($enum1));
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x01", $enumSet->getBinaryBitsetBe());
        $this->assertTrue($enumSet->contains($enum1));

        $this->assertNull($enumSet->attach($enum2));
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x03", $enumSet->getBinaryBitsetBe());
        $this->assertTrue($enumSet->contains($enum2));

        $this->assertNull($enumSet->attach($enum3));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x03", $enumSet->getBinaryBitsetBe());
        $this->assertTrue($enumSet->contains($enum3));

        $this->assertNull($enumSet->attach($enum4));
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x03", $enumSet->getBinaryBitsetBe());
        $this->assertTrue($enumSet->contains($enum4));
        
        $this->assertSame(4, $enumSet->count());

        $this->assertNull($enumSet->detach($enum2));
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x01", $enumSet->getBinaryBitsetBe());
        $this->assertFalse($enumSet->contains($enum2));
        
        $this->assertSame(3, $enumSet->count());
    }

    public function testSetBinaryBitsetLeBin()
    {
        $enumSet = new EnumSet(Enum65::class);
        $enumSet->setBinaryBitsetLe("\x01\x00\x00\x00\x00\x00\x00\x80\x01");

        $this->assertContains(Enum65::ONE(), $enumSet);
        $this->assertNotContains(Enum65::TWO(), $enumSet);
        $this->assertContains(Enum65::SIXTYFIVE(), $enumSet);
        $this->assertContains(Enum65::SIXTYFOUR(), $enumSet);
        $this->assertSame(3, $enumSet->count());
    }

    public function testSetBinaryBitsetLeBinShort()
    {
        $enumSet = new EnumSet(Enum65::class);
        $enumSet->setBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00\x00\x00\x00\x00\x00", $enumSet->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeBinLong()
    {
        $enumSet = new EnumSet(Enum65::class);
        $bitset = "\x0A\xFF\x00\x00\x00\x00\x00\x00\x00";
        $enumSet->setBinaryBitsetLe($bitset . "\x00\x00\x00\x00\x00\x00\x00");
        $this->assertSame($bitset, $enumSet->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeBinOutOfRangeBitsOfExtendedBytes1()
    {
        $enumSet = new EnumSet(Enum65::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $enumSet->setBinaryBitsetLe("\xff\xff\xff\xff\xff\xff\xff\xff\x00\x02");
    }

    public function testSetBinaryBitsetLeBinOutOfRangeBitsOfExtendedBytes2()
    {
        $enumSet = new EnumSet(Enum65::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $enumSet->setBinaryBitsetLe("\xff\xff\xff\xff\xff\xff\xff\xff\x02");
    }

    public function testSetBinaryBitsetLeBinOutOfRangeBitsOfLastValidByte()
    {
        // using Enum65 to detect Out-Of-Range bits of last valid byte
        // Enum65 has max. ordinal number of 2 of the last byte. -> 0001
        $enumSet   = new EnumSet(Enum65::class);
        $bitset    = $enumSet->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\x02";

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $enumSet->setBinaryBitsetLe($newBitset);
    }

    public function testSetBinaryBitsetLeBinArgumentExceptionIfNotString()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $enum = new EnumSet(Enum65::class);
        $enum->setBinaryBitsetLe(0);
    }

    public function testSetBinaryBitsetLeInt()
    {
        $enumSet = new EnumSet(Enum32::class);
        $enumSet->setBinaryBitsetLe("\x01\x00\x80\x01");
        $this->assertContains(Enum32::ONE(), $enumSet);
        $this->assertNotContains(Enum32::TWO(), $enumSet);
        $this->assertContains(Enum32::TWENTYFOUR(), $enumSet);
        $this->assertContains(Enum32::TWENTYFIVE(), $enumSet);
        $this->assertSame(3, $enumSet->count());
    }

    public function testSetBinaryBitsetLeIntShort()
    {
        $enumSet = new EnumSet(Enum32::class);
        $enumSet->setBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00", $enumSet->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeIntOutOfRangeBitsOfExtendedBytes1()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $enumSet->setBinaryBitsetLe("\x0A\xFF\x02");
    }

    public function testSetBinaryBitsetLeIntOutOfRangeBitsOfExtendedBytes2()
    {
        $enumSet = new EnumSet(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $enumSet->setBinaryBitsetLe("\x01\x01\x01\x01\x01\x01\x01\x01\x01");
    }

    public function testSetBinaryBitsetLeIntOutOfRangeBitsOfLastValidByte()
    {
        // using Enum65 to detect Out-Of-Range bits of last valid byte
        // Enum65 has max. ordinal number of 2 of the last byte. -> 0001
        $enumSet   = new EnumSet(Enum31::class);
        $bitset    = $enumSet->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\xFF";

        $this->expectException(InvalidArgumentException::class, 'Out-Of-Range');
        $enumSet->setBinaryBitsetLe($newBitset);
    }

    public function testSetBinaryBitsetBe()
    {
        $enumSet = new EnumSet(Enum65::class);
        $enumSet->setBinaryBitsetBe("\x01\x80\x00\x00\x00\x00\x00\x00\x01");

        $this->assertTrue($enumSet->contains(Enum65::ONE));
        $this->assertFalse($enumSet->contains(Enum65::TWO));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFIVE));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFOUR));
        $this->assertTrue($enumSet->count() == 3);
    }

    public function testSetBinaryBitsetBeArgumentExceptionIfNotString()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $enum = new EnumSet(Enum65::class);
        $enum->setBinaryBitsetBe(0);
    }

    public function testCountingEmptyEnumEmptySet()
    {
        $set = new EnumSet(EmptyEnum::class);
        $this->assertSame(0, $set->count());
    }

    public function testCountSingleBit32()
    {
        foreach (Enum32::getEnumerators() as $enum) {
            $enumSet = new EnumSet(Enum32::class);
            $enumSet->attach($enum);
            $this->assertSame(1, $enumSet->count());
        }
    }

    public function testCountSingleBit64()
    {
        foreach (Enum64::getEnumerators() as $enum) {
            $enumSet = new EnumSet(Enum64::class);
            $enumSet->attach($enum);
            $this->assertSame(1, $enumSet->count());
        }
    }

    public function testCountSingleBit66()
    {
        foreach (Enum66::getEnumerators() as $enum) {
            $enumSet = new EnumSet(Enum66::class);
            $enumSet->attach($enum);
            $this->assertSame(1, $enumSet->count());
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

    public function testGetOrdinalsIntDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getOrdinals();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
    }

    public function testGetOrdinalsBinDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet(Enum66::class);
        $set->attach(Enum66::ONE);
        $set->attach(Enum66::TWO);
        $set->next();

        $set->getOrdinals();
        $this->assertSame(Enum66::TWO, $set->current()->getValue());
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

    public function testGetEnumeratorsDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getEnumerators();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
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

    public function testGetValuesDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getValues();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
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

    public function testGetNamesDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getNames();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
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
