<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * Enumeration with mixed constant visibility added in PHP-7.1
 * and inheritance
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2017 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class ConstVisibilityEnumExtended extends ConstVisibilityEnum
{
    const IPUB2 = 'indirect public extended';
    public const PUB2 = 'public extended';
    protected const PRO2 = 'protected extended';
    private const PRI2 = 'private extended';
}
