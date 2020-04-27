<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use MabeEnum\EnumMap;
use MabeEnumTest\TestAsset\Enum32;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\EnumMapExt;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Unit tests for the class MabeEnum\EnumMap
 *
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumMapTest extends TestCase
{
    public function testInstantiate(): void
    {
        $enum1  = EnumBasic::ONE();
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO();
        $value2 = 'value2';

        /** @var callable(): iterable<EnumBasic, mixed> $loopInitializeMap */
        $loopInitializeMap = function () use ($enum1, $value1, $enum2, $value2): iterable {
            yield $enum1 => $value1;
            yield $enum2 => $value2;
        };

        $enumMap = new EnumMap(EnumBasic::class, $loopInitializeMap());
        $this->assertSame([$enum1, $enum2], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());
        $this->assertSame(2, $enumMap->count());
    }

    public function testBasicAddRemoveEnumeratorInstances(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $this->assertSame(EnumBasic::class, $enumMap->getEnumeration());

        $enum1  = EnumBasic::TWO();
        $value1 = 'value1';

        $enum2  = EnumBasic::ONE();
        $value2 = 'value2';

        $this->assertFalse($enumMap->has($enum1));
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());

        $enumMap->add($enum1, $value1);
        $this->assertTrue($enumMap->has($enum1));
        $this->assertSame($value1, $enumMap->get($enum1));
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([$enum1], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());

        $enumMap->add($enum2, $value2);
        $this->assertTrue($enumMap->has($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));
        $this->assertSame([$enum1, $enum2], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap->remove($enum1);
        $this->assertFalse($enumMap->has($enum1));
        $this->assertSame([$enum2], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $enumMap->remove($enum2);
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testBasicAddRemoveEnumeratorValues(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE;
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO;
        $value2 = 'value2';

        $this->assertFalse($enumMap->has($enum1));
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());

        $enumMap->add($enum1, $value1);
        $this->assertTrue($enumMap->has($enum1));
        $this->assertSame($value1, $enumMap->get($enum1));
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([EnumBasic::byValue($enum1)], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());

        $enumMap->add($enum2, $value2);
        $this->assertTrue($enumMap->has($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));
        $this->assertSame([EnumBasic::byValue($enum1), EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap->remove($enum1);
        $this->assertFalse($enumMap->has($enum1));
        $this->assertSame([EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $enumMap->remove($enum2);
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testBasicWithWithoutEnumeratorValues(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE;
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO;
        $value2 = 'value2';

        $this->assertFalse($enumMap->has($enum1));
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());

        $enumMap = $enumMap->with($enum1, $value1);
        $this->assertTrue($enumMap->has($enum1));
        $this->assertSame($value1, $enumMap->get($enum1));
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([EnumBasic::byValue($enum1)], $enumMap->getKeys());
        $this->assertSame([$value1], $enumMap->getValues());

        $enumMap = $enumMap->with($enum2, $value2);
        $this->assertTrue($enumMap->has($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));
        $this->assertSame([EnumBasic::byValue($enum1), EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap = $enumMap->without($enum1);
        $this->assertFalse($enumMap->has($enum1));
        $this->assertSame([EnumBasic::byValue($enum2)], $enumMap->getKeys());
        $this->assertSame([$value2], $enumMap->getValues());

        $enumMap = $enumMap->without($enum2);
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testBasicAddRemoveIterable(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE();
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO();
        $value2 = 'value2';

        $loopKeyValues = function () use ($enum1, $value1, $enum2, $value2): iterable {
            yield $enum1 => $value1;
            yield $enum2 => $value2;
        };
        $loopKeys = function () use ($enum1, $enum2): iterable {
            yield $enum1;
            yield $enum2;
        };

        $enumMap->addIterable($loopKeyValues());
        $this->assertTrue($enumMap->has($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));
        $this->assertSame([$enum1, $enum2], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap->removeIterable($loopKeys());
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testBasicWithWithoutIterable(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $enum1  = EnumBasic::ONE();
        $value1 = 'value1';

        $enum2  = EnumBasic::TWO();
        $value2 = 'value2';

        $loopKeyValues = function () use ($enum1, $value1, $enum2, $value2): iterable {
            yield $enum1 => $value1;
            yield $enum2 => $value2;
        };
        $loopKeys = function () use ($enum1, $enum2): iterable {
            yield $enum1;
            yield $enum2;
        };

        $enumMap = $enumMap->withIterable($loopKeyValues());
        $this->assertTrue($enumMap->has($enum2));
        $this->assertSame($value2, $enumMap->get($enum2));
        $this->assertSame([$enum1, $enum2], $enumMap->getKeys());
        $this->assertSame([$value1, $value2], $enumMap->getValues());

        $enumMap = $enumMap->withoutIterable($loopKeys());
        $this->assertFalse($enumMap->has($enum2));
        $this->assertSame([], $enumMap->getKeys());
        $this->assertSame([], $enumMap->getValues());
    }

    public function testGetMissingKeyThrowException(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->expectException(UnexpectedValueException::class);
        $enumMap->get(EnumBasic::ONE);
    }

    public function testIterate(): void
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
        $enumMap->add($enum1, $value1);
        $enumMap->add($enum2, $value2);
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

    public function testArrayAccessOffsetGetMissingKeyReturnsNull(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $this->assertNull($enumMap->offsetGet(EnumBasic::ONE));
    }

    public function testArrayAccessWithObjects(): void
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

    public function testArrayAccessWithValues(): void
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

    public function testArrayAccessOffsetSetThrowsInvalidArgumentExceptionOnInvalidEnumerator(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->expectException(InvalidArgumentException::class);
        $enumMap->offsetSet(EnumInheritance::INHERITANCE(), 'test');
    }

    public function testConstructThrowsInvalidArgumentExceptionIfEnumClassDoesNotExtendBaseEnum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new EnumMap('stdClass');
    }

    public function testContainsAndOffsetExistsReturnsFalseOnInvalidEnumerator(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);

        $this->assertFalse($enumMap->has(EnumInheritance::INHERITANCE()));
        $this->assertFalse($enumMap->has(EnumInheritance::INHERITANCE));

        $this->assertFalse(isset($enumMap[EnumInheritance::INHERITANCE()]));
        $this->assertFalse(isset($enumMap[EnumInheritance::INHERITANCE]));
    }

    public function testSearch(): void
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

    public function testSearchStrict(): void
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

    public function testNullValue(): void
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
        $this->assertTrue($enumMap->has(EnumBasic::ONE));
        $this->assertTrue($enumMap->has(EnumBasic::ONE()));
        $this->assertTrue($enumMap->has(EnumBasic::TWO));
        $this->assertTrue($enumMap->has(EnumBasic::TWO()));
        $this->assertFalse($enumMap->has(EnumBasic::THREE));
        $this->assertFalse($enumMap->has(EnumBasic::THREE()));

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

        $this->assertTrue($enumMap->has(EnumBasic::ONE));
        $this->assertTrue($enumMap->has(EnumBasic::ONE()));
        $this->assertTrue($enumMap->has(EnumBasic::TWO));
        $this->assertTrue($enumMap->has(EnumBasic::TWO()));
        $this->assertFalse($enumMap->has(EnumBasic::THREE));
        $this->assertFalse($enumMap->has(EnumBasic::THREE()));
    }

    public function testSerializable(): void
    {
        $enumMap = new EnumMap(EnumBasic::class);
        $enumMap[EnumBasic::ONE()] = 'one';

        $enumMapCopy = unserialize(serialize($enumMap));
        $this->assertTrue($enumMapCopy->offsetExists(EnumBasic::ONE));
        $this->assertFalse($enumMapCopy->offsetExists(EnumBasic::TWO));
    }

    public function testIsEmpty(): void
    {
        /** @var array<int, string> $items2 */
        $items2 = array_combine(Enum32::getValues(), Enum32::getNames());
        $map1 = new EnumMap(Enum32::class, []);
        $map2 = new EnumMap(Enum32::class, $items2);

        $this->assertTrue($map1->isEmpty());
        $this->assertFalse($map2->isEmpty());

        $map1->add(Enum32::ONE(), 'first');
        $map2->removeIterable(Enum32::getEnumerators());

        $this->assertFalse($map1->isEmpty());
        $this->assertTrue($map2->isEmpty());
    }

    public function testDebugInfo(): void
    {
        $map = new EnumMapExt(EnumBasic::class);
        foreach (EnumBasic::getEnumerators() as $i => $enumerator) {
            $map->add($enumerator, $i);
        }

        $dbg = $map->__debugInfo();

        $privateEnumMapPrefix = "\0" . EnumMap::class . "\0";
        $privateEnumMapExtPrefix = "\0" . EnumMapExt::class . "\0";
        $protectedEnumMapExtPrefix = "\0*\0";
        $publicEnumMapExtPrefix = '';

        // assert real properties still exists
        $this->assertArrayHasKey("{$privateEnumMapPrefix}enumeration", $dbg);
        $this->assertArrayHasKey("{$privateEnumMapPrefix}map", $dbg);
        $this->assertArrayHasKey("{$privateEnumMapExtPrefix}priv", $dbg);
        $this->assertArrayHasKey("{$protectedEnumMapExtPrefix}prot", $dbg);
        $this->assertArrayHasKey("{$publicEnumMapExtPrefix}pub", $dbg);

        // assert virtual private property __pairs
        $this->assertArrayHasKey("{$privateEnumMapPrefix}__pairs", $dbg);
        $this->assertSame(array_map(function ($k, $v) {
            return [$k, $v];
        }, $map->getKeys(), $map->getValues()), $dbg["{$privateEnumMapPrefix}__pairs"]);
    }

    /* deprecated */

    public function testContains(): void
    {
        $set = new EnumMap(EnumBasic::class, [EnumBasic::ONE => '1']);

        $this->assertTrue($set->contains(EnumBasic::ONE));
        $this->assertFalse($set->contains(EnumBasic::TWO));
    }
}
