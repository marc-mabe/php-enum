<?php

namespace MabeEnumBench;

use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\Enum32;

/**
 * Benchmark an EnumSet with 32 defined enumerators that's using an integer bitset internally.
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSet32Bench extends AbstractEnumSetBench
{
    /**
     * Will be called before every subject
     */
    public function init()
    {
        $this->values      = Enum32::getValues();
        $this->enumerators = Enum32::getEnumerators();

        $this->emptySet = new EnumSet(Enum32::class);
        $this->fullSet  = new EnumSet(Enum32::class, $this->enumerators);
    }
}
