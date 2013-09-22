<?php

namespace MabeEnumTest;

use MabeEnum\Enum;
use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\EnumWithoutDefaultValue;
use MabeEnumTest\TestAsset\EnumInheritance;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Unit tests for the class MabeEnum\EnumSet
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSetTest extends TestCase
{
    public function testBasic()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue');
        $this->assertSame('MabeEnumTest\TestAsset\EnumWithoutDefaultValue', $enumSet->getEnumClass());

        $enum1  = EnumWithoutDefaultValue::ONE();
        $enum2  = EnumWithoutDefaultValue::TWO();

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
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue');

        $enum1  = EnumWithoutDefaultValue::ONE;
        $enum2  = EnumWithoutDefaultValue::TWO;

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
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue', EnumSet::UNIQUE);

        $enumSet->attach(EnumWithoutDefaultValue::ONE());
        $enumSet->attach(EnumWithoutDefaultValue::ONE);

        $enumSet->attach(EnumWithoutDefaultValue::TWO());
        $enumSet->attach(EnumWithoutDefaultValue::TWO);

        $this->assertSame(2, $enumSet->count());
    }

    public function testNotUnique()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue', 0);

        $enumSet->attach(EnumWithoutDefaultValue::ONE());
        $enumSet->attach(EnumWithoutDefaultValue::ONE);

        $enumSet->attach(EnumWithoutDefaultValue::TWO());
        $enumSet->attach(EnumWithoutDefaultValue::TWO);

        $this->assertSame(4, $enumSet->count());

        // detch remove all
        $enumSet->detach(EnumWithoutDefaultValue::ONE);
        $this->assertSame(2, $enumSet->count());
    }

    public function testIterateUnordered()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue', EnumSet::UNIQUE);

        $enum1  = EnumWithoutDefaultValue::ONE();
        $enum2  = EnumWithoutDefaultValue::TWO();

        // an empty enum set needs to be invalid, starting by 0
        $this->assertSame(0, $enumSet->count());
        $this->assertFalse($enumSet->valid());

        // attach
        $enumSet->attach($enum1);
        $enumSet->attach($enum2);

        // a not empty enum map should be valid, starting by 0 (if not iterated)
        $this->assertSame(2, $enumSet->count());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(0, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());

        // go to the next element (last)
        $this->assertNull($enumSet->next());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(1, $enumSet->key());
        $this->assertSame($enum2, $enumSet->current());

        // go to the next element (out of range)
        $this->assertNull($enumSet->next());
        $this->assertFalse($enumSet->valid());
        $this->assertSame(2, $enumSet->key());

        // rewind will set the iterator position back to 0
        $enumSet->rewind();
        $this->assertTrue($enumSet->valid());
        $this->assertSame(0, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());
    }

    public function testIterateOrdered()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue', EnumSet::UNIQUE | EnumSet::ORDERED);

        $enum1 = EnumWithoutDefaultValue::ONE();
        $enum2 = EnumWithoutDefaultValue::TWO();

        // an empty enum set needs to be invalid, starting by 0
        $this->assertSame(0, $enumSet->count());
        $this->assertFalse($enumSet->valid());

        // attach
        $enumSet->attach($enum2);
        $enumSet->attach($enum1);

        // a not empty enum map should be valid, starting by 0 (if not iterated)
        $this->assertSame(2, $enumSet->count());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(0, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());

        // go to the next element (last)
        $this->assertNull($enumSet->next());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(1, $enumSet->key());
        $this->assertSame($enum2, $enumSet->current());

        // go to the next element (out of range)
        $this->assertNull($enumSet->next());
        $this->assertFalse($enumSet->valid());
        $this->assertSame(2, $enumSet->key());

        // rewind will set the iterator position back to 0
        $enumSet->rewind();
        $this->assertTrue($enumSet->valid());
        $this->assertSame(0, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());
    }

    public function testIterateOrderedNotUnique()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue', EnumSet::ORDERED);

        $enum1 = EnumWithoutDefaultValue::ONE();
        $enum2 = EnumWithoutDefaultValue::TWO();

        // an empty enum set needs to be invalid, starting by 0
        $this->assertSame(0, $enumSet->count());
        $this->assertFalse($enumSet->valid());

        // attach
        $enumSet->attach($enum2);
        $enumSet->attach($enum1);
        $enumSet->attach($enum2);
        $enumSet->attach($enum1);

        // index 0
        $this->assertSame(4, $enumSet->count());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(0, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());

        // index 1
        $this->assertNull($enumSet->next());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(1, $enumSet->key());
        $this->assertSame($enum1, $enumSet->current());

        // index 2
        $this->assertNull($enumSet->next());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(2, $enumSet->key());
        $this->assertSame($enum2, $enumSet->current());

        // index 3 (last)
        $this->assertNull($enumSet->next());
        $this->assertTrue($enumSet->valid());
        $this->assertSame(3, $enumSet->key());
        $this->assertSame($enum2, $enumSet->current());

        // go to the next element (out of range)
        $this->assertNull($enumSet->next());
        $this->assertFalse($enumSet->valid());
        $this->assertSame(4, $enumSet->key());

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

        // index 1
        $enumSet->next();
        $this->assertSame($enum2, $enumSet->current());

        // detach enum of current index
        $enumSet->detach($enumSet->current());
        $this->assertSame($enum3, $enumSet->current());

        // detach enum of current index if the last index
        $enumSet->detach($enumSet->current());
        $this->assertFalse($enumSet->valid());
    }

    public function testConstructThrowsInvalidArgumentExceptionIfEnumClassDoesNotExtendBaseEnum()
    {
        $this->setExpectedException('InvalidArgumentException');
        new EnumSet('stdClass');
    }

    public function testInitEnumThrowsInvalidArgumentExceptionOnInvalidEnum()
    {
        $enumSet = new EnumSet('MabeEnumTest\TestAsset\EnumWithoutDefaultValue');
        $this->setExpectedException('InvalidArgumentException');
        $this->assertFalse($enumSet->contains(EnumInheritance::INHERITANCE()));
    }
}
