<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * A very basic enumeration class with mixed value types
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @method static static ONE()
 * @method static static TWO()
 * @method static static THREE()
 * @method static static FOUR()
 * @method static static FIVE()
 * @method static static SIX()
 * @method static static SEVEN()
 * @method static static EIGHT()
 * @method static static NINE()
 * @method static static ZERO()
 * @method static static FLOAT()
 * @method static static STR()
 * @method static static STR_EMPTY()
 * @method static static NIL()
 * @method static static BOOLEAN_TRUE()
 * @method static static BOOLEAN_FALSE()
 */
class EnumBasic extends Enum
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

    const ARR           = [0, 0.1, 'str', null, true, false];
    const FLOAT         = 0.123;
    const STR           = 'str';
    const STR_EMPTY     = '';
    const NIL           = null;
    const BOOLEAN_TRUE  = true;
    const BOOLEAN_FALSE = false;
}
