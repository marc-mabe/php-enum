<?php

declare(strict_types=1);

namespace MabeEnum;

use ReflectionClass;
use InvalidArgumentException;
use LogicException;

/**
 * Abstract base enumeration class.
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 */
abstract class Enum
{
    /**
     * The selected enumerator value
     *
     * @var null|bool|int|float|string|array
     */
    private $value;

    /**
     * The ordinal number of the enumerator
     *
     * @var null|int
     */
    private $ordinal;

    /**
     * A map of enumerator names and values by enumeration class
     *
     * @var array ["$class" => ["$name" => $value, ...], ...]
     */
    private static $constants = [];

    /**
     * A List of available enumerator names by enumeration class
     *
     * @var array ["$class" => ["$name0", ...], ...]
     */
    private static $names = [];

    /**
     * Already instantiated enumerators
     *
     * @var array ["$class" => ["$name" => $instance, ...], ...]
     */
    private static $instances = [];

    /**
     * Constructor
     *
     * @param null|bool|int|float|string|array $value   The value of the enumerator
     * @param int|null                         $ordinal The ordinal number of the enumerator
     */
    final private function __construct($value, $ordinal = null)
    {
        $this->value   = $value;
        $this->ordinal = $ordinal;
    }

    /**
     * Get the name of the enumerator
     *
     * @return string
     * @see getName()
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @throws LogicException Enums are not cloneable
     *                        because instances are implemented as singletons
     */
    final private function __clone()
    {
        throw new LogicException('Enums are not cloneable');
    }

    /**
     * @throws LogicException Enums are not serializable
     *                        because instances are implemented as singletons
     */
    final public function __sleep()
    {
        throw new LogicException('Enums are not serializable');
    }

    /**
     * @throws LogicException Enums are not serializable
     *                        because instances are implemented as singletons
     */
    final public function __wakeup()
    {
        throw new LogicException('Enums are not serializable');
    }

    /**
     * Get the value of the enumerator
     *
     * @return null|bool|int|float|string|array
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the name of the enumerator
     *
     * @return string
     */
    final public function getName()
    {
        return self::$names[static::class][$this->ordinal ?? $this->getOrdinal()];
    }

    /**
     * Get the ordinal number of the enumerator
     *
     * @return int
     */
    final public function getOrdinal()
    {
        if ($this->ordinal === null) {
            $ordinal = 0;
            $value   = $this->value;
            foreach (self::detectConstants(static::class) as $constValue) {
                if ($value === $constValue) {
                    break;
                }
                ++$ordinal;
            }

            $this->ordinal = $ordinal;
        }

        return $this->ordinal;
    }

    /**
     * Compare this enumerator against another and check if it's the same.
     *
     * @param static|null|bool|int|float|string|array $enumerator An enumerator object or value
     * @return bool
     */
    final public function is($enumerator)
    {
        return $this === $enumerator || $this->value === $enumerator

            // The following additional conditions are required only because of the issue of serializable singletons
            || ($enumerator instanceof static
                && \get_class($enumerator) === static::class
                && $enumerator->value === $this->value
            );
    }

    /**
     * Get an enumerator instance of the given enumerator value or instance
     *
     * @param static|null|bool|int|float|string|array $enumerator An enumerator object or value
     * @return static
     * @throws InvalidArgumentException On an unknown or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final public static function get($enumerator)
    {
        if ($enumerator instanceof static && \get_class($enumerator) === static::class) {
            return $enumerator;
        }

        return static::byValue($enumerator);
    }

    /**
     * Get an enumerator instance by the given value
     *
     * @param null|bool|int|float|string|array $value Enumerator value
     * @return static
     * @throws InvalidArgumentException On an unknown or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final public static function byValue($value)
    {
        if (!isset(self::$constants[static::class])) {
            self::detectConstants(static::class);
        }

        $name = \array_search($value, self::$constants[static::class], true);
        if ($name === false) {
            throw new InvalidArgumentException(sprintf(
                'Unknown value %s for enumeration %s',
                \is_scalar($value)
                    ? \var_export($value, true)
                    : 'of type ' . (\is_object($value) ? \get_class($value) : \gettype($value)),
                static::class
            ));
        }

        if (!isset(self::$instances[static::class][$name])) {
            self::$instances[static::class][$name] = new static(self::$constants[static::class][$name]);
        }

        return self::$instances[static::class][$name];
    }

    /**
     * Get an enumerator instance by the given name
     *
     * @param string $name The name of the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous values
     */
    final public static function byName(string $name)
    {
        if (isset(self::$instances[static::class][$name])) {
            return self::$instances[static::class][$name];
        }

        $const = static::class . "::{$name}";
        if (!\defined($const)) {
            throw new InvalidArgumentException("{$const} not defined");
        }

        return self::$instances[static::class][$name] = new static(\constant($const));
    }

    /**
     * Get an enumeration instance by the given ordinal number
     *
     * @param int $ordinal The ordinal number of the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid ordinal number
     * @throws LogicException           On ambiguous values
     */
    final public static function byOrdinal(int $ordinal)
    {
        if (!isset(self::$names[static::class])) {
            self::detectConstants(static::class);
        }

        if (!isset(self::$names[static::class][$ordinal])) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid ordinal number %s, must between 0 and %s',
                $ordinal,
                \count(self::$names[static::class]) - 1
            ));
        }

        $name = self::$names[static::class][$ordinal];
        if (isset(self::$instances[static::class][$name])) {
            return self::$instances[static::class][$name];
        }

        return self::$instances[static::class][$name] = new static(self::$constants[static::class][$name], $ordinal);
    }

    /**
     * Get a list of enumerator instances ordered by ordinal number
     *
     * @return static[]
     */
    final public static function getEnumerators()
    {
        if (!isset(self::$names[static::class])) {
            self::detectConstants(static::class);
        }
        return \array_map([static::class, 'byName'], self::$names[static::class]);
    }

    /**
     * Get a list of enumerator values ordered by ordinal number
     *
     * @return mixed[]
     */
    final public static function getValues()
    {
        return \array_values(self::detectConstants(static::class));
    }

    /**
     * Get a list of enumerator names ordered by ordinal number
     *
     * @return string[]
     */
    final public static function getNames()
    {
        if (!isset(self::$names[static::class])) {
            self::detectConstants(static::class);
        }
        return self::$names[static::class];
    }
    
    /**
     * Get a list of enumerator ordinal numbers
     *
     * @return int[]
     */
    final public static function getOrdinals()
    {
        $count = \count(self::detectConstants(static::class));
        return $count ? \range(0, $count - 1) : [];
    }

    /**
     * Get all available constants of the called class
     *
     * @return array
     * @throws LogicException On ambiguous constant values
     */
    final public static function getConstants()
    {
        return self::detectConstants(static::class);
    }

    /**
     * Test if the given enumerator is part of this enumeration
     * 
     * @param static|null|bool|int|float|string|array $enumerator
     * @return bool
     */
    final public static function has($enumerator)
    {
        return ($enumerator instanceof static && \get_class($enumerator) === static::class)
            || static::hasValue($enumerator);
    }

    /**
     * Test if the given enumerator value is part of this enumeration
     *
     * @param null|bool|int|float|string|array $value
     * @return bool
     */
    final public static function hasValue($value)
    {
        $constants = self::detectConstants(static::class);
        return \in_array($value, $constants, true);
    }

    /**
     * Test if the given enumerator name is part of this enumeration
     *
     * @param string $name
     * @return bool
     */
    final public static function hasName(string $name)
    {
        return \defined("static::{$name}");
    }

    /**
     * Detect all public available constants of given enumeration class
     *
     * @param string $class
     * @return array
     */
    private static function detectConstants($class)
    {
        if (!isset(self::$constants[$class])) {
            $reflection = new ReflectionClass($class);
            $constants  = [];

            do {
                $scopeConstants = [];
                // Enumerators must be defined as public class constants
                foreach ($reflection->getReflectionConstants() as $reflConstant) {
                    if ($reflConstant->isPublic()) {
                        $scopeConstants[ $reflConstant->getName() ] = $reflConstant->getValue();
                    }
                }

                $constants = $scopeConstants + $constants;
            } while (($reflection = $reflection->getParentClass()) && $reflection->name !== __CLASS__);

            assert(
                self::noAmbiguousValues($constants),
                "Ambiguous enumerator values detected for {$class}"
            );

            self::$constants[$class] = $constants;
            self::$names[$class] = \array_keys($constants);
        }

        return self::$constants[$class];
    }

    /**
     * Test that the given constants does not contain ambiguous values
     * @param array $constants
     * @return bool
     */
    private static function noAmbiguousValues($constants)
    {
        foreach ($constants as $value) {
            $names = \array_keys($constants, $value, true);
            if (\count($names) > 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an enumerator instance by the given name.
     *
     * This will be called automatically on calling a method
     * with the same name of a defined enumerator.
     *
     * @param string $method The name of the enumerator (called as method)
     * @param array  $args   There should be no arguments
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous constant values
     */
    final public static function __callStatic(string $method, array $args)
    {
        return static::byName($method);
    }
}
