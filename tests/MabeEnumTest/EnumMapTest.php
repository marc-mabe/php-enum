<?php

namespace MabeEnumTest;

use MabeEnum\Enum;
use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\EnumWithoutDefaultValue;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Unit tests for the class MabeEnum\EnumMap
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMapTest extends TestCase
{
    public function testBasic()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumWithoutDefaultValue');
        
        $enum1  = EnumWithoutDefaultValue::ONE();
        $value1 = 'value2';

        $enum2  = EnumWithoutDefaultValue::TWO();
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertNull($enumMap->attach($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);

        $this->assertFalse($enumMap->contains($enum2));
        $this->assertNull($enumMap->attach($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);

        $this->assertNull($enumMap->detach($enum1));
        $this->assertFalse($enumMap->contains($enum1));

        $this->assertNull($enumMap->detach($enum2));
        $this->assertFalse($enumMap->contains($enum2));
    }

    public function testBasicWithConstantValuesAsEnums()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumWithoutDefaultValue');

        $enum1  = EnumWithoutDefaultValue::ONE;
        $value1 = 'value2';

        $enum2  = EnumWithoutDefaultValue::TWO;
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertNull($enumMap->attach($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);

        $this->assertFalse($enumMap->contains($enum2));
        $this->assertNull($enumMap->attach($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);

        $this->assertNull($enumMap->detach($enum1));
        $this->assertFalse($enumMap->contains($enum1));

        $this->assertNull($enumMap->detach($enum2));
        $this->assertFalse($enumMap->contains($enum2));
    }

    public function testIterate()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumWithoutDefaultValue');

        $enum1  = EnumWithoutDefaultValue::ONE();
        $value1 = 'value2';

        $enum2  = EnumWithoutDefaultValue::TWO();
        $value2 = 'value2';

        // an empty enum map needs to be invalid, starting by 0
        $enumMap->rewind();
        $this->assertSame(0, $enumMap->count());
        $this->assertFalse($enumMap->valid());

        // attach
        $enumMap->attach($enum1, $value2);
        $enumMap->attach($enum2, $value1);

        // a not empty enum map should be valid, starting by 0 (if not iterated)
        $enumMap->rewind();
        $this->assertSame(2, $enumMap->count());
        $this->assertTrue($enumMap->valid());
        $this->assertSame(0, $enumMap->key());
        $this->assertSame($enum1, $enumMap->current());

        // go to the next element (last)
        $this->assertNull($enumMap->next());
        $this->assertTrue($enumMap->valid());
        $this->assertSame(1, $enumMap->key());
        $this->assertSame($enum2, $enumMap->current());

        // go to the next element (out of range)
        $this->assertNull($enumMap->next());
        $this->assertFalse($enumMap->valid());
        $this->assertSame(2, $enumMap->key());

        // rewind will set the iterator position back to 0
        $enumMap->rewind();
        $this->assertTrue($enumMap->valid());
        $this->assertSame(0, $enumMap->key());
        $this->assertSame($enum1, $enumMap->current());
    }
}
