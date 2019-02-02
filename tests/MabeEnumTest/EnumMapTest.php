<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Unit tests for the class MabeEnum\EnumMap
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
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
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());

        $this->assertNull($enumMap->offsetSet($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([$enum1], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());


        $this->assertNull($enumMap->offsetSet($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);
        $this->assertSame([$enum1, $enum2], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $this->assertNull($enumMap->offsetUnset($enum1));
        $this->assertFalse($enumMap->contains($enum1));
        $this->assertSame([$enum2], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $this->assertNull($enumMap->offsetUnset($enum2));
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testBasicWithEnumeratorValues()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE;
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO;
        $value2 = 'value2';

        $this->assertFalse($enumMap->contains($enum1));
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());

        $this->assertNull($enumMap->offsetSet($enum1, $value1));
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([EnumBasic::byValue($enum1)], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());

        $this->assertNull($enumMap->offsetSet($enum2, $value2));
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);
        $this->assertSame([EnumBasic::byValue($enum1), EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $this->assertNull($enumMap->offsetUnset($enum1));
        $this->assertFalse($enumMap->contains($enum1));
        $this->assertSame([EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $this->assertNull($enumMap->offsetUnset($enum2));
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testOffsetGetMissingKey()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->expectException(UnexpectedValueException::class);
        $enumMap->offsetGet(EnumBasic::ONE);
    }

    public function testOffsetGetMissingArrayKey()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->expectException(UnexpectedValueException::class);
        $enumMap->offsetGet(EnumBasic::ARR);
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
        $enumMap->offsetSet($enum1, $value1);
        $enumMap->offsetSet($enum2, $value2);

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
        $this->assertNull($enumMap->current());
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

    public function testSearch()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::TWO()] = '2';
        $enumMap[EnumBasic::THREE()] = '3';

        $this->assertSame(EnumBasic::TWO(), $enumMap->search('2'));
        $this->assertSame(EnumBasic::TWO(), $enumMap->search(2));
        $this->assertSame(EnumBasic::THREE(), $enumMap->search('3'));
        $this->assertSame(EnumBasic::THREE(), $enumMap->search(3));

        $this->assertNull($enumMap->search('4'));
        $this->assertNull($enumMap->search(4));
        $this->assertNull($enumMap->search('unknown'));
    }

    public function testSearchStrict()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::TWO()] = '2';
        $enumMap[EnumBasic::THREE()] = '3';

        $this->assertSame(EnumBasic::TWO(), $enumMap->search('2', true));
        $this->assertNull($enumMap->search(2, true));
        $this->assertSame(EnumBasic::THREE(), $enumMap->search('3', true));
        $this->assertNull($enumMap->search(3, true));

        $this->assertNull($enumMap->search('4', true));
        $this->assertNull($enumMap->search(4, true));
        $this->assertNull($enumMap->search('unknown', true));
    }

    public function testNullValue()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::ONE()] = null;

        $this->assertSame(1, $enumMap->count());
        $this->assertNull($enumMap[EnumBasic::ONE]);
        $this->assertNull($enumMap->offsetGet(EnumBasic::ONE));
        $this->assertSame([EnumBasic::ONE()], $enumMap->getKeys());

        $enumMap->rewind();
        $this->assertSame(1, $enumMap->count());
        $this->assertTrue($enumMap->valid());
        $this->assertSame(EnumBasic::ONE(), $enumMap->key());
        $this->assertNull($enumMap->current());

        $this->assertFalse(isset($enumMap[EnumBasic::ONE]));
        $this->assertFalse(isset($enumMap[EnumBasic::ONE()]));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::ONE));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::ONE()));
        $this->assertTrue($enumMap->contains(EnumBasic::ONE));
        $this->assertTrue($enumMap->contains(EnumBasic::ONE()));

        // add the same enumeration a second time should do nothing
        $enumMap->offsetSet(EnumBasic::ONE(), null);
        $this->assertSame(1, $enumMap->count());
        $this->assertSame([EnumBasic::ONE()], $enumMap->getKeys());

        // overwrite by non null value
        $enumMap->offsetSet(EnumBasic::ONE(), false);
        $this->assertSame(1, $enumMap->count());
        $this->assertSame([EnumBasic::ONE()], $enumMap->getKeys());

        $this->assertSame(1, $enumMap->count());
        $this->assertTrue($enumMap->valid());
        $this->assertSame(EnumBasic::ONE(), $enumMap->key());
        $this->assertFalse($enumMap->current());

        $this->assertTrue(isset($enumMap[EnumBasic::ONE]));
        $this->assertTrue(isset($enumMap[EnumBasic::ONE()]));
        $this->assertTrue($enumMap->offsetExists(EnumBasic::ONE));
        $this->assertTrue($enumMap->offsetExists(EnumBasic::ONE()));
        $this->assertTrue($enumMap->contains(EnumBasic::ONE));
        $this->assertTrue($enumMap->contains(EnumBasic::ONE()));
    }

    public function testSeek()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::ONE()] = 'one';
        $enumMap[EnumBasic::TWO()] = 'two';

        $this->assertSame(EnumBasic::ONE(), $enumMap->key());

        $enumMap->seek(1);
        $this->assertSame(EnumBasic::TWO(), $enumMap->key());

        $enumMap->seek(0);
        $this->assertSame(EnumBasic::ONE(), $enumMap->key());

        $this->expectException(OutOfBoundsException::class);
        $enumMap->seek(2);
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
