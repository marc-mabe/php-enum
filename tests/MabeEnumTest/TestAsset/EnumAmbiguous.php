<?php

/**
 * Unit tests for the class MabeEnum_Enum
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class MabeEnumTest_TestAsset_EnumAmbiguous extends MabeEnum_Enum
{
    const UNIQUE1    = 'unique1';
    const AMBIGUOUS1 = 1;
    const UNIQUE2    = 'unique2';
    const AMBIGUOUS2 = '1';
    const UNIQUE3    = 'unique3';
}
