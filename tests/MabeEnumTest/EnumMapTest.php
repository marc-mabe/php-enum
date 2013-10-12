<?php

namespace MabeEnumTest;

use MabeEnum\Enum;
use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

/**
 * Unit tests for the class MabeEnum\EnumMap
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMapTest extends TestCase
{
    public function testBasic()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');
        $this->assertSame('MabeEnumTest\TestAsset\EnumBasic', $enumMap->getEnumClass());

        $enum1  = EnumBasic::ONE();
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO();
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertNull($enumMap->attach($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);
        $this->assertSame(spl_object_hash($enum1), $enumMap->getHash($enum1));

        $this->assertFalse($enumMap->contains($enum2));
        $this->assertNull($enumMap->attach($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);
        $this->assertSame(spl_object_hash($enum2), $enumMap->getHash($enum2));

        $this->assertNull($enumMap->detach($enum1));
        $this->assertFalse($enumMap->contains($enum1));

        $this->assertNull($enumMap->detach($enum2));
        $this->assertFalse($enumMap->contains($enum2));
    }

    public function testBasicWithConstantValuesAsEnums()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $enum1  = EnumBasic::ONE;
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO;
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertNull($enumMap->attach($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);
        $this->assertSame(spl_object_hash(EnumBasic::ONE()), $enumMap->getHash($enum1));

        $this->assertFalse($enumMap->contains($enum2));
        $this->assertNull($enumMap->attach($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);
        $this->assertSame(spl_object_hash(EnumBasic::TWO()), $enumMap->getHash($enum2));

        $this->assertNull($enumMap->detach($enum1));
        $this->assertFalse($enumMap->contains($enum1));

        $this->assertNull($enumMap->detach($enum2));
        $this->assertFalse($enumMap->contains($enum2));
    }

    public function testIterate()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $enum1  = EnumBasic::ONE();
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO();
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

    public function testIterateWithFlags()
    {
        $enumMap = new EnumMap(
            'MabeEnumTest\TestAsset\EnumBasic',
            EnumMap::KEY_AS_INDEX | EnumMap::CURRENT_AS_ENUM
        );

        $enumMap->attach(EnumBasic::TWO(), 'first');
        $enumMap->attach(EnumBasic::ONE(), 'second');

        // EnumMap::KEY_AS_INDEX | EnumMap::CURRENT_AS_ENUM (first)
        $this->assertSame(EnumMap::KEY_AS_INDEX | EnumMap::CURRENT_AS_ENUM, $enumMap->getFlags());

        $enumMap->rewind();
        $this->assertSame(0, $enumMap->key());
        $this->assertSame(EnumBasic::TWO(), $enumMap->current());

        $enumMap->next();
        $this->assertSame(1, $enumMap->key());
        $this->assertSame(EnumBasic::ONE(), $enumMap->current());

        // EnumMap::KEY_AS_NAME | EnumMap::CURRENT_AS_DATA
        $enumMap->setFlags(EnumMap::KEY_AS_NAME | EnumMap::CURRENT_AS_DATA);
        $this->assertSame(EnumMap::KEY_AS_NAME | EnumMap::CURRENT_AS_DATA, $enumMap->getFlags());

        $enumMap->rewind();
        $this->assertSame('TWO', $enumMap->key());
        $this->assertSame('first', $enumMap->current());

        $enumMap->next();
        $this->assertSame('ONE', $enumMap->key());
        $this->assertSame('second', $enumMap->current());

        // EnumMap::KEY_AS_VALUE | EnumMap::CURRENT_AS_ORDINAL
        $enumMap->setFlags(EnumMap::KEY_AS_VALUE | EnumMap::CURRENT_AS_ORDINAL);
        $this->assertSame(EnumMap::KEY_AS_VALUE | EnumMap::CURRENT_AS_ORDINAL, $enumMap->getFlags());

        $enumMap->rewind();
        $this->assertSame(2, $enumMap->key());
        $this->assertSame(1, $enumMap->current());

        $enumMap->next();
        $this->assertSame(1, $enumMap->key());
        $this->assertSame(0, $enumMap->current());

        // EnumMap::KEY_AS_ORDINAL | EnumMap::CURRENT_AS_VALUE
        $enumMap->setFlags(EnumMap::KEY_AS_ORDINAL | EnumMap::CURRENT_AS_VALUE);
        $this->assertSame(EnumMap::KEY_AS_ORDINAL | EnumMap::CURRENT_AS_VALUE, $enumMap->getFlags());
    
        $enumMap->rewind();
        $this->assertSame(1, $enumMap->key());
        $this->assertSame(2, $enumMap->current());
    
        $enumMap->next();
        $this->assertSame(0, $enumMap->key());
        $this->assertSame(1, $enumMap->current());

        // only change current flag to EnumMap::CURRENT_AS_NAME
        $enumMap->setFlags(EnumMap::CURRENT_AS_NAME);
        $this->assertSame(EnumMap::KEY_AS_ORDINAL | EnumMap::CURRENT_AS_NAME, $enumMap->getFlags());

        $enumMap->rewind();
        $this->assertSame(1, $enumMap->key());
        $this->assertSame('TWO', $enumMap->current());

        $enumMap->next();
        $this->assertSame(0, $enumMap->key());
        $this->assertSame('ONE', $enumMap->current());

        // only change key flag to EnumMap::NAME_AS_NAME
        $enumMap->setFlags(EnumMap::KEY_AS_NAME);
        $this->assertSame(EnumMap::KEY_AS_NAME | EnumMap::CURRENT_AS_NAME, $enumMap->getFlags());

        $enumMap->rewind();
        $this->assertSame('TWO', $enumMap->key());
        $this->assertSame('TWO', $enumMap->current());

        $enumMap->next();
        $this->assertSame('ONE', $enumMap->key());
        $this->assertSame('ONE', $enumMap->current());
    }

    public function testArrayAccessWithObjects()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $enumMap[EnumBasic::ONE()] = 'first';
        $enumMap[EnumBasic::TWO()] = 'second';

        $this->assertTrue(isset($enumMap[EnumBasic::ONE()]));
        $this->assertTrue(isset($enumMap[EnumBasic::TWO()]));

        $this->assertSame('first', $enumMap[EnumBasic::ONE()]);
        $this->assertSame('second', $enumMap[EnumBasic::TWO()]);

        unset($enumMap[EnumBasic::ONE()], $enumMap[EnumBasic::TWO()]);

        $this->assertFalse(isset($enumMap[EnumBasic::ONE()]));
        $this->assertFalse(isset($enumMap[EnumBasic::TWO()]));
    }

    public function testArrayAccessWithValues()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $enumMap[EnumBasic::ONE] = 'first';
        $enumMap[EnumBasic::TWO] = 'second';

        $this->assertTrue(isset($enumMap[EnumBasic::ONE]));
        $this->assertTrue(isset($enumMap[EnumBasic::TWO]));

        $this->assertSame('first', $enumMap[EnumBasic::ONE]);
        $this->assertSame('second', $enumMap[EnumBasic::TWO]);

        unset($enumMap[EnumBasic::ONE], $enumMap[EnumBasic::TWO]);

        $this->assertFalse(isset($enumMap[EnumBasic::ONE]));
        $this->assertFalse(isset($enumMap[EnumBasic::TWO]));
    }

    public function testConstructThrowsInvalidArgumentExceptionIfEnumClassDoesNotExtendBaseEnum()
    {
        $this->setExpectedException('InvalidArgumentException');
        new EnumMap('stdClass');
    }

    public function testSetFlagsThrowsInvalidArgumentExceptionOnUnsupportedKeyFlag()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $this->setExpectedException('InvalidArgumentException');
        $enumMap->setFlags(5);
    }

    public function testCurrentThrowsRuntimeExceptionOnInvalidFlag()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');
        $enumMap->attach(EnumBasic::ONE());
        $enumMap->rewind();

        // change internal flags to an invalid current flag
        $reflectionClass = new ReflectionClass($enumMap);
        $reflectionProp  = $reflectionClass->getProperty('flags');
        $reflectionProp->setAccessible(true);
        $reflectionProp->setValue($enumMap, 0);

        $this->setExpectedException('RuntimeException');
        $enumMap->current();
    }

    public function testKeyThrowsRuntimeExceptionOnInvalidFlag()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');
        $enumMap->attach(EnumBasic::ONE());
        $enumMap->rewind();

        // change internal flags to an invalid current flag
        $reflectionClass = new ReflectionClass($enumMap);
        $reflectionProp  = $reflectionClass->getProperty('flags');
        $reflectionProp->setAccessible(true);
        $reflectionProp->setValue($enumMap, 0);

        $this->setExpectedException('RuntimeException');
        $enumMap->key();
    }

    public function testSetFlagsThrowsInvalidArgumentExceptionOnUnsupportedCurrentFlag()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $this->setExpectedException('InvalidArgumentException');
        $enumMap->setFlags(48);
    }

    public function testInitEnumThrowsInvalidArgumentExceptionOnInvalidEnumGiven()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $this->setExpectedException('InvalidArgumentException');
        $enumMap->offsetSet(EnumInheritance::INHERITANCE(), 'test');
    }

    public function testContainsAndOffsetExistsReturnsFalseOnInvalidEnum()
    {
        $enumMap = new EnumMap('MabeEnumTest\TestAsset\EnumBasic');

        $this->assertFalse($enumMap->contains(EnumInheritance::INHERITANCE()));
        $this->assertFalse($enumMap->contains(EnumInheritance::INHERITANCE));

        $this->assertFalse(isset($enumMap[EnumInheritance::INHERITANCE()]));
        $this->assertFalse(isset($enumMap[EnumInheritance::INHERITANCE]));
    }
}
