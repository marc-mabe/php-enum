<?php

namespace MabeEnumTest\TestAsset;

use MabeEnum\Enum;

/**
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
class FailureEnumWithArguments extends Enum
{
    const VALIDATION_ERROR = 'validation error';
    const DB_ERROR = 'db error';

    /**
     * @var string
     */
    private $message;

    /**
     * @param $message
     * @return FailureEnumWithArguments
     */
    public static function VALIDATION_ERROR($message)
    {
        $failure = self::byValue(self::VALIDATION_ERROR);
        $failure->message = $message;

        return $failure;
    }

    /**
     * @param string $message
     * @return FailureEnumWithArguments
     */
    public static function DB_ERROR($message)
    {
        $failure = self::byValue(self::DB_ERROR);
        $failure->message = $message;

        return $failure;
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
