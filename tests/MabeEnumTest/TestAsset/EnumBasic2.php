<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * Unit tests for the class MabeEnum\Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @method EnumBasic ONE()
 * @method EnumBasic TWO()
 * @method EnumBasic THREE()
 * @method EnumBasic FOUR()
 * @method EnumBasic FIVE()
 * @method EnumBasic SIX()
 * @method EnumBasic SEVEN()
 * @method EnumBasic EIGHT()
 * @method EnumBasic NINE()
 * @method EnumBasic ZERO()
 * @method EnumBasic FLOAT()
 * @method EnumBasic STR()
 * @method EnumBasic STR_EMPTY()
 * @method EnumBasic NIL()
 * @method EnumBasic BOOLEAN_TRUE()
 * @method EnumBasic BOOLEAN_FALSE()
 */
class EnumBasic2 extends Enum
{
    const ONE   = 1;
    const TWO   = 2;
    const THREE = 3;
    const FOUR  = 4;
    const FIVE  = 5;
    const SIX   = 6;
    const SEVEN = 7;
    const EIGHT = 8;
    const NINE  = 9;
    const ZERO  = 0;

    const FLOAT         = 0.123;
    const STR           = 'str';
    const STR_EMPTY     = '';
    const NIL           = null;
    const BOOLEAN_TRUE  = true;
    const BOOLEAN_FALSE = false;
}
