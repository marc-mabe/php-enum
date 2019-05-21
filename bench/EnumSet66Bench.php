<?php

namespace MabeEnumBench;

use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\Enum66;

/**
 * Benchmark an EnumSet with 66 defined enumerators that's using an binary bitset internally.
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSet66Bench extends AbstractEnumSetBench
{
    /**
     * Will be called before every subject
     */
    public function init()
    {
        $this->values      = Enum66::getValues();
        $this->enumerators = Enum66::getEnumerators();

        $this->emptySet = new EnumSet(Enum66::class);
        $this->fullSet  = new EnumSet(Enum66::class, $this->enumerators);
    }
}
