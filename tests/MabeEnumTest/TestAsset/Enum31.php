<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * Enumeration with numbers from 1-31 (Safe to use on 32 and 64 bit systems as positive integer)
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
 *
 * @method static static TEN()
 * @method static static ELEVEN()
 * @method static static TWELVE()
 * @method static static THIRTEEN()
 * @method static static FOURTEEN()
 * @method static static FIVETEEN()
 * @method static static SIXTEEN()
 * @method static static SEVENTEEN()
 * @method static static EIGHTEEN()
 * @method static static NINETEEN()
 *
 * @method static static TWENTY()
 * @method static static TWENTYONE()
 * @method static static TWENTYTWO()
 * @method static static TWENTYTHREE()
 * @method static static TWENTYFOUR()
 * @method static static TWENTYFIVE()
 * @method static static TWENTYSIX()
 * @method static static TWENTYSEVEN()
 * @method static static TWENTYEIGHT()
 * @method static static TWENTYNINE()
 *
 * @method static static THIRTY()
 * @method static static THERTYONE()
 */
class Enum31 extends Enum
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

    const TEN       = 10;
    const ELEVEN    = 11;
    const TWELVE    = 12;
    const THIRTEEN  = 13;
    const FOURTEEN  = 14;
    const FIVETEEN  = 15;
    const SIXTEEN   = 16;
    const SEVENTEEN = 17;
    const EIGHTEEN  = 18;
    const NINETEEN  = 19;
    const TWENTY    = 20;

    const TWENTYONE   = 21;
    const TWENTYTWO   = 22;
    const TWENTTHREE  = 23;
    const TWENTYFOUR  = 24;
    const TWENTYFIVE  = 25;
    const TWENTYSIX   = 26;
    const TWENTYSEVEN = 27;
    const TWENTYEIGHT = 28;
    const TWENTYNINE  = 29;

    const THIRTY    = 30;
    const THERTYONE = 31;
}
