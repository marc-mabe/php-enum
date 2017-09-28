<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Serializable;

/**
 * Unit tests for the class MabeEnum\EnumMap
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMapTest extends TestCase
{
    public function testBasicWithEnumeratorInstances()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $this->assertSame(EnumBasic::class, $enumMap->getEnumeration());

        $enum1  = EnumBasic::TWO();
        $value1 = 'value1';

        $enum2  = EnumBasic::ONE();
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

    public function testBasicWithEnumeratorValues()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE;
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO;
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
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE();
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO();
        $value2 = 'value2';

        // an empty enum map needs to be invalid, starting by 0
        $enumMap->rewind();
        $this->assertSame(0, $enumMap->count());
        $this->assertFalse($enumMap->valid());

        // attach
        $enumMap->attach($enum1, $value1);
        $enumMap->attach($enum2, $value2);

        // a not empty enum map should be valid, starting by 0 (if not iterated)
        $enumMap->rewind();
        $this->assertSame(2, $enumMap->count());
        $this->assertTrue($enumMap->valid());
        $this->assertSame($enum1, $enumMap->key());
        $this->assertSame($value1, $enumMap->current());

        // go to the next element (last)
        $this->assertNull($enumMap->next());
        $this->assertTrue($enumMap->valid());
        $this->assertSame($enum2, $enumMap->key());
        $this->assertSame($value2, $enumMap->current());

        // go to the next element (out of range)
        $this->assertNull($enumMap->next());
        $this->assertFalse($enumMap->valid());
        $this->assertSame(null, $enumMap->key());

        // rewind will set the iterator position back to 0
        $enumMap->rewind();
        $this->assertTrue($enumMap->valid());
        $this->assertSame($enum1, $enumMap->key());
        $this->assertSame($value1, $enumMap->current());
    }

    public function testArrayAccessWithObjects()
    {
        $enumMap = new EnumMap(EnumBasic::class);

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
        $enumMap = new EnumMap(EnumBasic::class);

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
        $this->expectException(InvalidArgumentException::class);
        new EnumMap('stdClass');
    }

    public function testInitEnumThrowsInvalidArgumentExceptionOnInvalidEnumGiven()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $enumMap->offsetSet(EnumInheritance::INHERITANCE(), 'test');
    }

    public function testContainsAndOffsetExistsReturnsFalseOnInvalidEnum()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->assertFalse($enumMap->contains(EnumInheritance::INHERITANCE()));
        $this->assertFalse($enumMap->contains(EnumInheritance::INHERITANCE));

        $this->assertFalse(isset($enumMap[EnumInheritance::INHERITANCE()]));
        $this->assertFalse(isset($enumMap[EnumInheritance::INHERITANCE]));
    }

    public function testSerializable()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::ONE()] = 'one';

        $enumMapCopy = unserialize(serialize($enumMap));
        $this->assertTrue($enumMapCopy->offsetExists(EnumBasic::ONE));
        $this->assertFalse($enumMapCopy->offsetExists(EnumBasic::TWO));

        // unserialized instance should be the same
        $this->assertSame(EnumBasic::ONE(), $enumMapCopy->key());
    }
}
