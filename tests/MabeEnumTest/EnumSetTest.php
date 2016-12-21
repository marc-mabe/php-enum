<?php

namespace MabeEnumTest;

use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\EmptyEnum;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\Enum32;
use MabeEnumTest\TestAsset\Enum64;
use MabeEnumTest\TestAsset\Enum65;
use MabeEnumTest\TestAsset\Enum66;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Unit tests for the class MabeEnum\EnumSet
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSetTest extends TestCase
{
    public function testBasic()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame('MabeEnumTest\TestAsset\EnumBasic', $enumSet->getEnumeration());

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

    public function testDeprecatedGetEnumClass()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame('MabeEnumTest\TestAsset\EnumBasic', $enumSet->getEnumClass());
    }

    public function testBasicWithConstantValuesAsEnums()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');

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
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');

        $enumSet->attach(EnumBasic::ONE());
        $enumSet->attach(EnumBasic::ONE);

        $enumSet->attach(EnumBasic::TWO());
        $enumSet->attach(EnumBasic::TWO);

        $this->assertSame(2, $enumSet->count());
    }

    public function testIterateOrdered()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');

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
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumInheritance');

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
        $this->setExpectedException('InvalidArgumentException');
        new EnumSet('stdClass');
    }

    public function testInitEnumThrowsInvalidArgumentExceptionOnInvalidEnum()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->setExpectedException('InvalidArgumentException');
        $this->assertFalse($enumSet->contains(EnumInheritance::INHERITANCE()));
    }

    public function testIterateOutOfRangeIfLastOrdinalEnumIsSet()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
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

    public function testRewindFirstOnEmptySet()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');

        $enumSet->attach(EnumBasic::TWO);
        $enumSet->rewind();
        $this->assertGreaterThan(0, $enumSet->key());

        $enumSet->detach(EnumBasic::TWO);
        $enumSet->rewind();
        $this->assertSame(0, $enumSet->key());
    }

    public function test32EnumerationsSet()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum32');
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
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum64');
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
        $enum = new EnumSet('MabeEnumTest\TestAsset\Enum65');

        $this->assertNull($enum->attach(Enum65::byOrdinal(64)));
        $enum->next();
        $this->assertTrue($enum->valid());
    }

    public function testGetBinaryBitsetLe()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        
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
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        
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

    /**
     * @deprecated
     */
    public function testGetBitset()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        
        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $this->assertNull($enumSet->attach($enum1));
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x01", $enumSet->getBitset());
        $this->assertTrue($enumSet->contains($enum1));

        $this->assertNull($enumSet->attach($enum2));
        $this->assertSame("\x00\x00\x00\x00\x00\x00\x00\x00\x03", $enumSet->getBitset());
        $this->assertTrue($enumSet->contains($enum2));

        $this->assertNull($enumSet->attach($enum3));
        $this->assertSame("\x01\x00\x00\x00\x00\x00\x00\x00\x03", $enumSet->getBitset());
        $this->assertTrue($enumSet->contains($enum3));

        $this->assertNull($enumSet->attach($enum4));
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x03", $enumSet->getBitset());
        $this->assertTrue($enumSet->contains($enum4));
        
        $this->assertSame(4, $enumSet->count());

        $this->assertNull($enumSet->detach($enum2));
        $this->assertSame("\x01\x80\x00\x00\x00\x00\x00\x00\x01", $enumSet->getBitset());
        $this->assertFalse($enumSet->contains($enum2));
        
        $this->assertSame(3, $enumSet->count());
    }

    public function testSetBinaryBitsetLe()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enumSet->setBinaryBitsetLe("\x01\x00\x00\x00\x00\x00\x00\x80\x01");

        $this->assertTrue($enumSet->contains(Enum65::ONE));
        $this->assertFalse($enumSet->contains(Enum65::TWO));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFIVE));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFOUR));
        $this->assertTrue($enumSet->count() == 3);
    }

    public function testSetBinaryBitsetLeTruncateHighBits()
    {
        // using Enum66 to make sure the max. ordinal number gets converted into a bitset
        // Enum65 has max. ordinal number of 1 of the last byte. -> 00000001
        // Enum66 has max. ordinal number of 2 of the last byte. -> 00000011
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum66');
        foreach (Enum66::getEnumerators() as $enumerator) {
            $enumSet->attach($enumerator);
        }

        $bitset    = $enumSet->getBinaryBitsetLe();
        $newBitset = substr($bitset, 0, -1) . "\xff\xff";
        $enumSet->setBinaryBitsetLe($newBitset);

        $this->assertSame(bin2hex($bitset), bin2hex($enumSet->getBinaryBitsetLe()));
    }

    public function testSetBinaryBitsetBe()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enumSet->setBinaryBitsetBe("\x01\x80\x00\x00\x00\x00\x00\x00\x01");

        $this->assertTrue($enumSet->contains(Enum65::ONE));
        $this->assertFalse($enumSet->contains(Enum65::TWO));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFIVE));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFOUR));
        $this->assertTrue($enumSet->count() == 3);
    }

    /**
     * @deprecated
     */
    public function testSetBitset()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enumSet->setBitset("\x01\x80\x00\x00\x00\x00\x00\x00\x01");

        $this->assertTrue($enumSet->contains(Enum65::ONE));
        $this->assertFalse($enumSet->contains(Enum65::TWO));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFIVE));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFOUR));
        $this->assertTrue($enumSet->count() == 3);
    }

    public function testSetBinaryBitsetLeShort()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enumSet->setBinaryBitsetLe("\x0A");
        $this->assertSame("\x0A\x00\x00\x00\x00\x00\x00\x00\x00", $enumSet->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeLong()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $enumSet->setBinaryBitsetLe("\x0A\xFF\xFF\xFF\xFF\xFF");
        $this->assertSame("\x0A\xFF", $enumSet->getBinaryBitsetLe());
    }

    public function testSetBinaryBitsetLeArgumentExceptionIfNotString()
    {
        $this->setExpectedException('InvalidArgumentException');
        
        $enum = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enum->setBinaryBitsetLe(0);
    }

    public function testSetBinaryBitsetBeArgumentExceptionIfNotString()
    {
        $this->setExpectedException('InvalidArgumentException');
        
        $enum = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enum->setBinaryBitsetBe(0);
    }

    public function testCountingEmptyEnumEmptySet()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EmptyEnum');
        $this->assertSame(0, $set->count());
    }

    public function testIsEqual()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
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
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumInheritance');
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
    public function testIsSubsetEqual()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $this->assertTrue($set1->isSubset($set2));

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $set2->attach($enumerator);
            $this->assertTrue($set1->isSubset($set2));
        }
    }

    public function testIsSubsetFull()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set2->attach($enumerator);
            $this->assertTrue($set1->isSubset($set2));
        }
    }

    public function testIsSubsetFalse()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isSubset($set2));
        }
    }

    public function testIsSubsetWrongInstance()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumInheritance');
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
    public function testIsSsetEqual()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $this->assertTrue($set1->isSuperset($set2));

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $set2->attach($enumerator);
            $this->assertTrue($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetFull()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertTrue($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetFalse()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\Enum32');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        foreach (Enum32::getEnumerators() as $enumerator) {
            $set2->attach($enumerator);
            $this->assertFalse($set1->isSuperset($set2));
        }
    }

    public function testIsSupersetWrongInstance()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumInheritance');
        $this->assertFalse($set1->isSuperset($set2));

        foreach (EnumBasic::getEnumerators() as $enumerator) {
            $set1->attach($enumerator);
            $this->assertFalse($set1->isSuperset($set2));

            $set2->attach($enumerator->getValue());
            $this->assertFalse($set1->isSuperset($set2));
        }
    }

    public function testGetOrdinals()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame(array(), $set->getOrdinals());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(range(0, count(EnumBasic::getConstants()) - 1), $set->getOrdinals());
    }

    public function testGetOrdinalsDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getOrdinals();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
    }

    public function testGetEnumerators()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame(array(), $set->getEnumerators());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(EnumBasic::getEnumerators(), $set->getEnumerators());
    }

    public function testGetEnumeratorsDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getEnumerators();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
    }

    public function testGetValues()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame(array(), $set->getValues());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(array_values(EnumBasic::getConstants()), $set->getValues());
    }

    public function testGetValuesDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getValues();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
    }

    public function testGetNames()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame(array(), $set->getNames());

        foreach (EnumBasic::getConstants() as $value) {
            $set->attach($value);
        }

        $this->assertSame(array_keys(EnumBasic::getConstants()), $set->getNames());
    }

    public function testGetNamesDoesNotEffectIteratorPosition()
    {
        $set = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set->attach(EnumBasic::ONE);
        $set->attach(EnumBasic::TWO);
        $set->next();

        $set->getNames();
        $this->assertSame(EnumBasic::TWO, $set->current()->getValue());
    }

    public function testUnion()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);

        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);

        $set3 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set3->attach(EnumBasic::THREE);
        $set3->attach(EnumBasic::FOUR);

        $rs = $set1->union($set2, $set3);
        $this->assertSame(array(
            EnumBasic::ONE,
            EnumBasic::TWO,
            EnumBasic::THREE,
            EnumBasic::FOUR,
        ), $rs->getValues());
    }

    public function testUnionThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        $this->setExpectedException('InvalidArgumentException');
        $set1->union($set2);
    }

    public function testIntersect()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);
        $set1->attach(EnumBasic::THREE);

        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $set3 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set3->attach(EnumBasic::THREE);
        $set3->attach(EnumBasic::FOUR);
        $set3->attach(EnumBasic::FIVE);

        $rs = $set1->intersect($set2, $set3);
        $this->assertSame(array(
            EnumBasic::THREE,
        ), $rs->getValues());
    }

    public function testIntersectThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        $this->setExpectedException('InvalidArgumentException');
        $set1->intersect($set2);
    }

    public function testDiff()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);
        $set1->attach(EnumBasic::THREE);

        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $set3 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set3->attach(EnumBasic::THREE);
        $set3->attach(EnumBasic::FOUR);
        $set3->attach(EnumBasic::FIVE);

        $rs = $set1->diff($set2, $set3);
        $this->assertSame(array(
            EnumBasic::ONE,
        ), $rs->getValues());
    }

    public function testDiffThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        $this->setExpectedException('InvalidArgumentException');
        $set1->diff($set2);
    }

    public function testSymDiff()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set1->attach(EnumBasic::ONE);
        $set1->attach(EnumBasic::TWO);
        $set1->attach(EnumBasic::THREE);

        $set2 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2->attach(EnumBasic::TWO);
        $set2->attach(EnumBasic::THREE);
        $set2->attach(EnumBasic::FOUR);

        $set3 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set3->attach(EnumBasic::THREE);
        $set3->attach(EnumBasic::FOUR);
        $set3->attach(EnumBasic::FIVE);

        $rs = $set1->symDiff($set2, $set3);
        $this->assertSame(array(
            EnumBasic::ONE,
            EnumBasic::FOUR,
            EnumBasic::FIVE,
        ), $rs->getValues());
    }

    public function testSymDiffThrowsInvalidArgumentException()
    {
        $set1 = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $set2 = new EnumSet('MabeEnumTest\TestAsset\Enum32');

        $this->setExpectedException('InvalidArgumentException');
        $set1->symDiff($set2);
    }

    public function testBitset(){
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');

        $enum1  = EnumBasic::ONE;
        $enum2  = EnumBasic::TWO;

        $this->assertNull($enumSet->attach($enum1));
        $this->assertTrue($enumSet->getBitset()==(1<<EnumBasic::get($enum1)->getOrdinal()));

        $this->assertNull($enumSet->attach($enum2));
        $this->assertTrue($enumSet->getBitset()==(1<<EnumBasic::get($enum1)->getOrdinal() | 1<<EnumBasic::get($enum2)->getOrdinal()));


        $this->assertNull($enumSet->detach($enum1));
        $bitset=$enumSet->getBitset();
        $enumSet2=new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $enumSet2->setBitset($bitset);

        $this->assertFalse($enumSet2->contains($enum1));
        $this->assertTrue($enumSet2->contains($enum2));
    }
}
