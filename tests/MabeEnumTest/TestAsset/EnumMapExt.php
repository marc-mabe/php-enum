<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\EnumMap;

/**
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2020 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumMapExt extends EnumMap
{
    private $priv = 'private';
    protected $prot = 'protected';
    public $pub = 'public';
}
