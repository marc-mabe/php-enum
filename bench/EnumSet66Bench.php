<?php

namespace MabeEnumBench;

use MabeEnum\EnumSet;
use MabeEnumTest\TestAsset\Enum66;

/**
 * Benchmark of EnumSet used with an enumeration of 66 enumerators.
 * (The internal bitset have to be a binary string)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
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
        $this->fullSet  = new EnumSet(Enum66::class);
        foreach ($this->enumerators as $enumerator) {
            $this->fullSet->attach($enumerator);
        }
    }
}
