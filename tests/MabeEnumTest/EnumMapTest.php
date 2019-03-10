<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
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

        $enumMap->offsetSet($enum1, $value1);
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([$enum1], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());

        $enumMap->offsetSet($enum2, $value2);
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);
        $this->assertSame([$enum1, $enum2], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap->offsetUnset($enum1);
        $this->assertFalse($enumMap->contains($enum1));
        $this->assertSame([$enum2], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $enumMap->offsetUnset($enum2);
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

        $enumMap->offsetSet($enum1, $value1);
        $this->assertTrue($enumMap->contains($enum1));
        $this->assertSame($value1, $enumMap[$enum1]);
        $this->assertFalse($enumMap->contains($enum2));
        $this->assertSame([EnumBasic::byValue($enum1)], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());

        $enumMap->offsetSet($enum2, $value2);
        $this->assertTrue($enumMap->contains($enum2));
        $this->assertSame($value2, $enumMap[$enum2]);
        $this->assertSame([EnumBasic::byValue($enum1), EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap->offsetUnset($enum1);
        $this->assertFalse($enumMap->contains($enum1));
        $this->assertSame([EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $enumMap->offsetUnset($enum2);
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

        // an iterator of an empty enum map needs to be invalid
        $it = $enumMap->getIterator();
        $this->assertFalse($it->valid());
        $this->assertNull($it->key());
        $this->assertNull($it->current());

        // set two values
        $enumMap->offsetSet($enum1, $value1);
        $enumMap->offsetSet($enum2, $value2);
        $this->assertSame(2, $enumMap->count());

        // a not empty enum map should be valid, starting by 0 (if not iterated)
        $it = $enumMap->getIterator();
        $this->assertTrue($it->valid());
        $this->assertSame($enum1, $it->key());
        $this->assertSame($value1, $it->current());

        // go to the next element (last)
        $it->next();
        $this->assertTrue($it->valid());
        $this->assertSame($enum2, $it->key());
        $this->assertSame($value2, $it->current());

        // go to the next element (out of range)
        $it->next();
        $this->assertNull($it->current());
        $this->assertFalse($it->valid());
        $this->assertSame(null, $it->key());
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

    public function testOffsetSetThrowsInvalidArgumentExceptionOnInvalidEnumerator()
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $enumMap->offsetSet(EnumInheritance::INHERITANCE(), 'test');
    }

    public function testContainsAndOffsetExistsReturnsFalseOnInvalidEnumerator()
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
        $enumMap[EnumBasic::TWO()] = null;

        $this->assertSame(2, $enumMap->count());
        $this->assertNull($enumMap[EnumBasic::ONE]);
        $this->assertNull($enumMap->offsetGet(EnumBasic::ONE));
        $this->assertSame([EnumBasic::ONE(), EnumBasic::TWO()], $enumMap->getKeys());
        $this->assertSame([null, null], $enumMap->getValues());

        // test iterator works with null values
        $it = $enumMap->getIterator();
        $this->assertTrue($it->valid());
        $this->assertSame(EnumBasic::ONE(), $it->key());
        $this->assertNull($it->current());

        $it->next();
        $this->assertTrue($it->valid());
        $this->assertSame(EnumBasic::TWO(), $it->key());
        $this->assertNull($it->current());

        $it->next();
        $this->assertFalse($it->valid());
        $this->assertNull($it->key());
        $this->assertNull($it->current());

        // isses returns false for non existing keys and for NULL values
        $this->assertFalse(isset($enumMap[EnumBasic::ONE]));
        $this->assertFalse(isset($enumMap[EnumBasic::ONE()]));
        $this->assertFalse(isset($enumMap[EnumBasic::TWO]));
        $this->assertFalse(isset($enumMap[EnumBasic::TWO()]));
        $this->assertFalse(isset($enumMap[EnumBasic::THREE]));
        $this->assertFalse(isset($enumMap[EnumBasic::THREE()]));

        // offsetExists returns false for non existing keys and for NULL values
        $this->assertFalse($enumMap->offsetExists(EnumBasic::ONE));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::ONE()));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::TWO));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::TWO()));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::THREE));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::THREE()));

        // contains returns false for non existing keys and true for existing keys no matter of the value
        $this->assertTrue($enumMap->contains(EnumBasic::ONE));
        $this->assertTrue($enumMap->contains(EnumBasic::ONE()));
        $this->assertTrue($enumMap->contains(EnumBasic::TWO));
        $this->assertTrue($enumMap->contains(EnumBasic::TWO()));
        $this->assertFalse($enumMap->contains(EnumBasic::THREE));
        $this->assertFalse($enumMap->contains(EnumBasic::THREE()));

        // add the same enumeration and value a second time should do nothing
        $enumMap->offsetSet(EnumBasic::ONE(), null);
        $this->assertSame(2, $enumMap->count());
        $this->assertSame([EnumBasic::ONE(), EnumBasic::TWO()], $enumMap->getKeys());
        $this->assertSame([null, null], $enumMap->getValues());

        // overwrite by non null value
        $enumMap->offsetSet(EnumBasic::ONE(), false);
        $this->assertSame(2, $enumMap->count());
        $this->assertSame([EnumBasic::ONE(), EnumBasic::TWO()], $enumMap->getKeys());
        $this->assertSame([false, null], $enumMap->getValues());

        $it = $enumMap->getIterator();
        $this->assertTrue($it->valid());
        $this->assertSame(EnumBasic::ONE(), $it->key());
        $this->assertFalse($it->current());

        $it->next();
        $this->assertTrue($it->valid());
        $this->assertSame(EnumBasic::TWO(), $it->key());
        $this->assertNull($it->current());

        $this->assertTrue(isset($enumMap[EnumBasic::ONE]));
        $this->assertTrue(isset($enumMap[EnumBasic::ONE()]));
        $this->assertFalse(isset($enumMap[EnumBasic::TWO]));
        $this->assertFalse(isset($enumMap[EnumBasic::TWO()]));
        $this->assertFalse(isset($enumMap[EnumBasic::THREE]));
        $this->assertFalse(isset($enumMap[EnumBasic::THREE()]));

        $this->assertTrue($enumMap->offsetExists(EnumBasic::ONE));
        $this->assertTrue($enumMap->offsetExists(EnumBasic::ONE()));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::TWO));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::TWO()));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::THREE));
        $this->assertFalse($enumMap->offsetExists(EnumBasic::THREE()));

        $this->assertTrue($enumMap->contains(EnumBasic::ONE));
        $this->assertTrue($enumMap->contains(EnumBasic::ONE()));
        $this->assertTrue($enumMap->contains(EnumBasic::TWO));
        $this->assertTrue($enumMap->contains(EnumBasic::TWO()));
        $this->assertFalse($enumMap->contains(EnumBasic::THREE));
        $this->assertFalse($enumMap->contains(EnumBasic::THREE()));
    }

    public function testSerializable()
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::ONE()] = 'one';

        $enumMapCopy = unserialize(serialize($enumMap));
        $this->assertTrue($enumMapCopy->offsetExists(EnumBasic::ONE));
        $this->assertFalse($enumMapCopy->offsetExists(EnumBasic::TWO));
    }
}
