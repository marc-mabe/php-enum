<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * Enumeration with mixed constant visibility added in PHP-7.1
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright 2020, Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 *
 * @method static static IPUB()
 * @method static static PUB()
 */
class ConstVisibilityEnum extends Enum
{
    const IPUB = 'indirect public';
    public const PUB = 'public';
    protected const PRO = 'protected';

    /** @phpstan-ignore-next-line */
    private const PRI = 'private';
}
