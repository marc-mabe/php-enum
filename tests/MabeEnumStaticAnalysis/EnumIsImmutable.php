<?php

declare(strict_types=1);

namespace MabeEnumStaticAnalysis;

/**
 * This is a static analysis fixture to verify that the API signature
 * of an enum allows for pure operations. Almost all methods will seem to be
 * redundant or trivial: that's normal, we're just verifying the
 * transitivity of immutable type signatures.
 *
 * Please note that this does not guarantee that the internals of the enum
 * library are pure/safe, but just that the declared API to the outside world
 * is seen as immutable.
 */
final class EnumIsImmutable
{
    /** @psalm-pure */
    public static function stringCastIsPure(): string
    {
        return DummyEnum::a()
            ->__toString();
    }

    /**
     * @psalm-pure
     *
     * @psalm-return never-return
     */
    public static function sleepIsPure(): void
    {
        DummyEnum::a()
            ->__sleep();
    }

    /**
     * @psalm-pure
     *
     * @psalm-return never-return
     */
    public static function wakeUpIsPure(): void
    {
        DummyEnum::a()
            ->__wakeup();
    }

    /**
     * @psalm-pure
     *
     * @psalm-return non-empty-string
     */
    public static function nameRetrievalIsPure(): string
    {
        return DummyEnum::a()
            ->getName();
    }

    /**
     * @return null|bool|int|float|string|array<int|string, mixed>
     *
     * @psalm-pure
     */
    public static function valueRetrievalIsPure()
    {
        return DummyEnum::a()
            ->getValue();
    }

    /** @psalm-pure */
    public static function getIsPure(): DummyEnum
    {
        return DummyEnum::get(DummyEnum::A);
    }

    /** @psalm-pure */
    public static function ordinalRetrievalIsPure(): int
    {
        return DummyEnum::a()
            ->getOrdinal();
    }

    /** @psalm-pure */
    public static function comparisonIsPure(): bool
    {
        return DummyEnum::a()->is(DummyEnum::b());
    }

    /** @psalm-pure */
    public static function byValueIsPure(): DummyEnum
    {
        return DummyEnum::byValue('A_VALUE');
    }

    /** @psalm-pure */
    public static function byNameIsPure(): DummyEnum
    {
        return DummyEnum::byValue('A');
    }

    /** @psalm-pure */
    public static function byOrdinalIsPure(): DummyEnum
    {
        return DummyEnum::byOrdinal(1);
    }

    /**
     * @psalm-pure
     *
     * @psalm-return list<DummyEnum>
     */
    public static function getEnumeratorsIsPure(): array
    {
        return DummyEnum::getEnumerators();
    }

    /**
     * @psalm-pure
     *
     * @psalm-return list<null|bool|int|float|string|array>
     */
    public static function getValuesIsPure(): array
    {
        return DummyEnum::getValues();
    }

    /**
     * @return array<int, string>
     *
     * @psalm-pure
     *
     * @psalm-return list<non-empty-string>
     */
    public static function getNamesIsPure(): array
    {
        return DummyEnum::getNames();
    }

    /**
     * @psalm-pure
     *
     * @psalm-return list<int>
     */
    public static function getOrdinalsIsPure(): array
    {
        return DummyEnum::getOrdinals();
    }

    /** @psalm-pure */
    public static function hasIsPure(): bool
    {
        return DummyEnum::has('a');
    }

    /** @psalm-pure */
    public static function hasValueIsPure(): bool
    {
        return DummyEnum::hasValue('A_VALUE');
    }

    /** @psalm-pure */
    public static function hasNameIsPure(): bool
    {
        return DummyEnum::hasName('A');
    }

    /** @psalm-pure */
    public static function callStaticIsPure(): DummyEnum
    {
        return DummyEnum::__callStatic('a', []);
    }
}
