<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\EnumMap;

/**
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2020 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @extends EnumMap<\MabeEnum\Enum>
 */
class EnumMapExt extends EnumMap
{
    /** @var string */
    private $priv = 'private';

    /** @var string */
    protected $prot = 'protected';

    /** @var string */
    public $pub = 'public';
}
