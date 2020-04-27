<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * Enumeration with numbers from 1-65 (For > 64 bit bitset)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @method static static SIXTYFIVE()
 */
class Enum65 extends Enum64
{
    const SIXTYFIVE = 65;
}
