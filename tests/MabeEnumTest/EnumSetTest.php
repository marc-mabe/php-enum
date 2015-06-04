<?php

namespace MabeEnumTest;

use MabeEnum\Enum;
use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\Enum32;
use MabeEnumTest\TestAsset\Enum64;
use MabeEnumTest\TestAsset\Enum65;
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
        $enum    = EnumBasic::getByOrdinal(count(EnumBasic::getConstants()) - 1);

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

        $this->assertNull($enum->attach(Enum65::getByOrdinal(64)));
        $enum->next();
        $this->assertTrue($enum->valid());
    }

    public function testGetBitset()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        
        $enum1 = Enum65::ONE;
        $enum2 = Enum65::TWO;
        $enum3 = Enum65::SIXTYFIVE;
        $enum4 = Enum65::SIXTYFOUR;

        $this->assertNull($enumSet->attach($enum1));
        $this->assertSame('000000000000000001', \bin2hex($enumSet->getBitset()));
        $this->assertTrue($enumSet->contains($enum1));

        $this->assertNull($enumSet->attach($enum2));
        $this->assertSame('000000000000000003', \bin2hex($enumSet->getBitset()));
        $this->assertTrue($enumSet->contains($enum2));

        $this->assertNull($enumSet->attach($enum3));
        $this->assertSame('010000000000000003', \bin2hex($enumSet->getBitset()));
        $this->assertTrue($enumSet->contains($enum3));

        $this->assertNull($enumSet->attach($enum4));
        $this->assertSame('018000000000000003', \bin2hex($enumSet->getBitset()));
        $this->assertTrue($enumSet->contains($enum4));
        
        $this->assertSame(4, $enumSet->count());

        $this->assertNull($enumSet->detach($enum2));
        $this->assertSame('018000000000000001', \bin2hex($enumSet->getBitset()));
        $this->assertFalse($enumSet->contains($enum2));
        
        $this->assertSame(3, $enumSet->count());
    }

    public function testSetBitset()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enumSet->setBitset(\hex2bin('018000000000000001'));

        $this->assertTrue($enumSet->contains(Enum65::ONE));
        $this->assertFalse($enumSet->contains(Enum65::TWO));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFIVE));
        $this->assertTrue($enumSet->contains(Enum65::SIXTYFOUR));
        $this->assertTrue($enumSet->count() == 3);
    }

    public function testSetBitsetShort()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enumSet->setBitset(\hex2bin('0A'));
        $this->assertSame('00000000000000000a', \bin2hex($enumSet->getBitset()));
    }

    public function testSetBitsetLong()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumBasic');
        $enumSet->setBitset(\hex2bin('FFFFFFFFFF0A'));
        $this->assertSame('ff0a', \bin2hex($enumSet->getBitset()));
    }

    public function testFalseBitsetArgumentExceptionIfNotString()
    {
        $this->setExpectedException('InvalidArgumentException');
        
        $enum = new EnumSet('MabeEnumTest\TestAsset\Enum65');
        $enum->setBitset(0);
    }
}
