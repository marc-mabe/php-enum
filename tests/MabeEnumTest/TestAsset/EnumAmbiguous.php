<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * Unit tests for the class MabeEnum\Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumAmbiguous extends Enum
{
    const UNIQUE1        = 'unique1';
    const AMBIGUOUS_INT1 = 1;
    const UNIQUE2        = 'unique2';
    const AMBIGUOUS_INT2 = 1;
    const UNIQUE3        = 'unique3';

    const AMBIGUOUS_STR1 = '1';
    const AMBIGUOUS_STR2 = '1';

    const AMBIGUOUS_NULL1 = null;
    const AMBIGUOUS_NULL2 = null;

    const AMBIGUOUS_FALSE1 = false;
    const AMBIGUOUS_FALSE2 = false;
}
