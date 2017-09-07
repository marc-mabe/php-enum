<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use Serializable;

/**
 * Basic serializable enumeration
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class SerializableEnum extends Enum implements Serializable
{
    use EnumSerializableTrait;

    const INT = 0;
    const NIL = null;
    const STR = '';
}
