<?php

declare(strict_types=1);

namespace MabeEnumStaticAnalysis;

use MabeEnum\Enum;
use phpDocumentor\Reflection\Types\Static_;

/**
 * @psalm-immutable enums are immutable
 */
final class DummyEnum extends Enum
{
    public const A = 'A_VALUE';
    public const B = 'B_VALUE';

    /** @psalm-pure */
    public static function a(): self
    {
        return self::get(self::A);
    }

    /** @psalm-pure */
    public static function b(): self
    {
        return self::get(self::B);
    }
}
