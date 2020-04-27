<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use Serializable;

/**
 * Basic serializable enumeration
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @method static static INT()
 * @method static static NIL()
 * @method static static STR()
 */
class SerializableEnum extends Enum implements Serializable
{
    use EnumSerializableTrait;

    const INT = 0;
    const NIL = null;
    const STR = '';
}
