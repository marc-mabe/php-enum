<?php

namespace MabeEnumTest;

use InvalidArgumentException;
use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\EmptyEnum;
use MabeEnumTest\TestAsset\Enum31;
use MabeEnumTest\TestAsset\EnumBasic;
use MabeEnumTest\TestAsset\EnumInheritance;
use MabeEnumTest\TestAsset\Enum32;
use MabeEnumTest\TestAsset\Enum64;
use MabeEnumTest\TestAsset\Enum65;
use MabeEnumTest\TestAsset\Enum66;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the class MabeEnum\EnumSet
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
class EnumSetIteratorTest extends TestCase
{
    public function testIterateEmpty()
    {
        $set = new EnumSet(EnumBasic::class);

        $this->assertSame([], iterator_to_array($set->getIterator()));
    }

    public function testIterateOrdered()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->add(EnumBasic::FOUR);
        $set->add(EnumBasic::TWO);
        $set->add(EnumBasic::SEVEN);

        $this->assertSame([
            1 => EnumBasic::TWO(),
            3 => EnumBasic::FOUR(),
            6 => EnumBasic::SEVEN(),
        ], iterator_to_array($set));
    }

    public function testMultipleIterators()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->add(EnumBasic::ONE);
        $set->add(EnumBasic::TWO);

        $it1 = $set->getIterator();
        $it2 = $set->getIterator();
        $it2->next();

        $this->assertSame(0, $it1->key());
        $this->assertSame(1, $it2->key());
    }

    public function testStartAtFirstValidPosition()
    {
        $set = new EnumSet(EnumBasic::class);
        $set->add(EnumBasic::SEVEN);

        $it = $set->getIterator();
        $this->assertSame(EnumBasic::SEVEN(), $it->current());
        $this->assertSame(EnumBasic::SEVEN()->getOrdinal(), $it->key());
    }

    public function testCurrentOnEmpty()
    {
        $set = new EnumSet(EnumBasic::class);

        $it = $set->getIterator();
        $this->assertFalse($it->valid());
        $this->assertNull($it->key());
        $this->assertNull($it->current());
    }

    /**
     * @param string $enumeration
     * @dataProvider getIntegerEnumerations
     */
    public function testNextCurrentOutOfRange(string $enumeration)
    {
        $set   = new EnumSet($enumeration);
        $count = count($enumeration::getConstants());
        $last  = $enumeration::byOrdinal($count - 1);
        $set->add($last);

        $it = $set->getIterator();
        $this->assertTrue($it->valid());
        $this->assertSame($last, $it->current());
        $this->assertSame($last->getOrdinal(), $it->key());

        // go to the first out-of-range position
        $it->next();
        $this->assertFalse($it->valid());
        $this->assertNull($it->key());
        $this->assertNull($it->current());
    }

    /**
     * Data provider for all available integer enumerators
     * @return array
     */
    public function getIntegerEnumerations()
    {
        return [
            [Enum31::class],
            [Enum32::class],
            [Enum64::class],
            [Enum65::class],
            [Enum66::class]
        ];
    }
}
