<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\EnumSet;

/**
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @extends EnumSet<\MabeEnum\Enum>
 */
class EnumSetExt extends EnumSet
{
    /** @var string */
    private $priv = 'private';

    /** @var string */
    protected $prot = 'protected';

    /** @var string */
    public $pub = 'public';
}
