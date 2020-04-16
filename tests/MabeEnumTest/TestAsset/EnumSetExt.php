<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\EnumSet;

/**
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2020 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class EnumSetExt extends EnumSet
{
    private $priv = 'private';
    protected $prot = 'protected';
    public $pub = 'public';
}
