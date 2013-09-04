<?php

/**
 * Unit tests for the class MabeEnum_Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class MabeEnumTest_EnumMapTest extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $enumMap = new MabeEnum_EnumMap('MabeEnumTest_TestAsset_EnumWithoutDefaultValue');
        
        $enum1  = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $value1 = 'value2';

        $enum2  = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::TWO);
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertNull($enumMap->attach($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap->get($enum1));

        $this->assertFalse($enumMap->contains($enum2));
        $this->assertNull($enumMap->attach($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));

        $this->assertNull($enumMap->detach($enum1));
        $this->assertFalse($enumMap->contains($enum1));

        $this->assertNull($enumMap->detach($enum2));
        $this->assertFalse($enumMap->contains($enum2));
    }

    public function testBasicWithConstantValuesAsEnums()
    {
        $enumMap = new MabeEnum_EnumMap('MabeEnumTest_TestAsset_EnumWithoutDefaultValue');

        $enum1  = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE;
        $value1 = 'value2';

        $enum2  = MabeEnumTest_TestAsset_EnumWithoutDefaultValue::TWO;
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertNull($enumMap->attach($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap->get($enum1));

        $this->assertFalse($enumMap->contains($enum2));
        $this->assertNull($enumMap->attach($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));

        $this->assertNull($enumMap->detach($enum1));
        $this->assertFalse($enumMap->contains($enum1));

        $this->assertNull($enumMap->detach($enum2));
        $this->assertFalse($enumMap->contains($enum2));
    }

    public function testIterate()
    {
        $enumMap = new MabeEnum_EnumMap('MabeEnumTest_TestAsset_EnumWithoutDefaultValue');

        $enum1  = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::ONE);
        $value1 = 'value2';

        $enum2  = new MabeEnumTest_TestAsset_EnumWithoutDefaultValue(MabeEnumTest_TestAsset_EnumWithoutDefaultValue::TWO);
        $value2 = 'value2';

        // an empty enum map needs to be invalid, starting by 0
        $this->assertSame(0, $enumMap->count());
        $this->assertFalse($enumMap->valid());

        // attach in revert order shouldn't change ordering of iteration
        $enumMap->attach($enum2, $value2);
        $enumMap->attach($enum1, $value1);

        // a not empty enum map should be valid, starting by 0 (if not iterated)
        $this->assertSame(2, $enumMap->count());
        $this->assertTrue($enumMap->valid());
        $this->assertSame(0, $enumMap->currentPosition());
        $this->assertSame($enum1, $enumMap->key());
        $this->assertSame($value1, $enumMap->current());

        // go to the next element (last)
        $this->assertNull($enumMap->next());
        $this->assertTrue($enumMap->valid());
        $this->assertSame(1, $enumMap->currentPosition());
        $this->assertSame($enum2, $enumMap->key());
        $this->assertSame($value2, $enumMap->current());

        // go to the next element (out of range)
        $this->assertNull($enumMap->next());
        $this->assertFalse($enumMap->valid());
        $this->assertSame(2, $enumMap->currentPosition());
        //$this->assertSame($enum2, $enumMap->currentEnum());
        //$this->assertSame($value2, $enumMap->currentValue());
    }
}
