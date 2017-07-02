<?php

namespace MabeEnum;

use ReflectionClass;
use InvalidArgumentException;
use LogicException;

/**
 * Class to implement enumerations for PHP 5 (without SplEnum)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2015 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
abstract class Enum
{
    /**
     * The selected enumerator value
     *
     * @var null|bool|int|float|string
     */
    private $value;

    /**
     * The ordinal number of the enumerator
     *
     * @var null|int
     */
    private $ordinal;

    /**
     * An array of available constants by class
     *
     * @var array ["$class" => ["$name" => $value, ...], ...]
     */
    private static $constants = array();

    /**
     * Already instantiated enumerators
     *
     * @var array ["$class" => ["$name" => $instance, ...], ...]
     */
    private static $instances = array();

    /**
     * Constructor
     *
     * @param null|bool|int|float|string $value   The value of the enumerator
     * @param int|null                   $ordinal The ordinal number of the enumerator
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
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Will return same instance of enum
     * this is useful when using assets generators
     *
     * @return Enum
     */
    final public function __clone()
    {
        return $this;
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
     * @return null|bool|int|float|string
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
        return array_search($this->value, self::detectConstants(get_called_class()), true);
    }

    /**
     * Get the ordinal number of the enumerator
     *
     * @return int
     */
    final public function getOrdinal()
    {
        if ($this->ordinal !== null) {
            return $this->ordinal;
        }

        // detect ordinal
        $ordinal = 0;
        $value   = $this->value;
        foreach (self::detectConstants(get_called_class()) as $constValue) {
            if ($value === $constValue) {
                break;
            }
            ++$ordinal;
        }

        $this->ordinal = $ordinal;
        return $ordinal;
    }

    /**
     * Compare this enumerator against another and check if it's the same.
     *
     * @param mixed $enumerator
     * @return bool
     */
    final public function is($enumerator)
    {
        return $this === $enumerator || $this->value === $enumerator

            // The following additional conditions are required only because of the issue of serializable singletons
            || ($enumerator instanceof static
                && get_class($enumerator) === get_called_class()
                && $enumerator->value === $this->value
            );
    }

    /**
     * Get an enumerator instance of the given value or instance
     *
     * @param static|null|bool|int|float|string $value
     * @return static
     * @throws InvalidArgumentException On an unknwon or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final public static function get($value)
    {
        if ($value instanceof static && get_class($value) === get_called_class()) {
            return $value;
        }

        return static::byValue($value);
    }

    /**
     * Get an enumerator instance by the given value
     *
     * @param mixed $value
     * @return static
     * @throws InvalidArgumentException On an unknwon or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final public static function byValue($value)
    {
        $class     = get_called_class();
        $constants = self::detectConstants($class);
        $name      = array_search($value, $constants, true);
        if ($name === false) {
            $message = is_scalar($value)
                ? 'Unknown value ' . var_export($value, true)
                : 'Invalid value of type ' . (is_object($value) ? get_class($value) : gettype($value));
            throw new InvalidArgumentException($message);
        }

        if (!isset(self::$instances[$class][$name])) {
            self::$instances[$class][$name] = new $class($constants[$name]);
        }

        return self::$instances[$class][$name];
    }

    /**
     * Get an enumerator instance by the given name
     *
     * @param string $name The name of the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous values
     */
    final public static function byName($name)
    {
        $name  = (string) $name;
        $class = get_called_class();
        if (isset(self::$instances[$class][$name])) {
            return self::$instances[$class][$name];
        }

        $const = $class . '::' . $name;
        if (!defined($const)) {
            throw new InvalidArgumentException($const . ' not defined');
        }

        return self::$instances[$class][$name] = new $class(constant($const));
    }

    /**
     * Get an enumeration instance by the given ordinal number
     *
     * @param int $ordinal The ordinal number or the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid ordinal number
     * @throws LogicException           On ambiguous values
     */
    final public static function byOrdinal($ordinal)
    {
        $ordinal   = (int) $ordinal;
        $class     = get_called_class();
        $constants = self::detectConstants($class);
        $item      = array_slice($constants, $ordinal, 1, true);
        if (empty($item)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid ordinal number, must between 0 and %s',
                count($constants) - 1
            ));
        }

        $name = key($item);
        if (isset(self::$instances[$class][$name])) {
            return self::$instances[$class][$name];
        }

        return self::$instances[$class][$name] = new $class(current($item), $ordinal);
    }

    /**
     * Get an enumerator instance by the given name
     *
     * @param string $name The name of the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous values
     * @deprecated
     */
    final public static function getByName($name)
    {
        return static::byName($name);
    }

    /**
     * Get an enumeration instance by the given ordinal number
     *
     * @param int $ordinal The ordinal number or the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid ordinal number
     * @throws LogicException           On ambiguous values
     * @deprecated
     */
    final public static function getByOrdinal($ordinal)
    {
        return static::byOrdinal($ordinal);
    }

    /**
     * Clear all instantiated enumerators of the called class
     *
     * NOTE: This can break singleton behavior ... use it with caution!
     *
     * @return void
     * @deprecated
     */
    final public static function clear()
    {
        $class = get_called_class();
        unset(self::$instances[$class], self::$constants[$class]);
    }

    /**
     * Get a list of enumerator instances ordered by ordinal number
     *
     * @return static[]
     */
    final public static function getEnumerators()
    {
        return array_map('self::byName', array_keys(self::detectConstants(get_called_class())));
    }

    /**
     * Get a list of enumerator values ordered by ordinal number
     *
     * @return mixed[]
     */
    final public static function getValues()
    {
        return array_values(self::detectConstants(get_called_class()));
    }

    /**
     * Get a list of enumerator names ordered by ordinal number
     *
     * @return string[]
     */
    final public static function getNames()
    {
        return array_keys(self::detectConstants(get_called_class()));
    }
    /*
     * Get a list of enumerator ordinal numbers
     *
     * @return int[]
     */
    final public static function getOrdinals()
    {
        $count = count(self::detectConstants(get_called_class()));
        return $count === 0 ? array() : range(0, $count - 1);
    }

    /**
     * Get all available constants of the called class
     *
     * @return array
     * @throws LogicException On ambiguous constant values
     */
    final public static function getConstants()
    {
        return self::detectConstants(get_called_class());
    }

    /**
     * Is the given enumerator part of this enumeration
     * 
     * @param static|null|bool|int|float|string $value
     * @return bool
     */
    final public static function has($value)
    {
        if ($value instanceof static && get_class($value) === get_called_class()) {
            return true;
        }

        $class     = get_called_class();
        $constants = self::detectConstants($class);

        return in_array($value, $constants, true);
    }

    /**
     * Detect all available constants by the given class
     *
     * @param string $class
     * @return array
     * @throws LogicException On ambiguous constant values
     */
    private static function detectConstants($class)
    {
        if (!isset(self::$constants[$class])) {
            $reflection = new ReflectionClass($class);
            $constants  = array();

            do {
                $scopeConstants = array();
                if (PHP_VERSION_ID >= 70100) {
                    // Since PHP-7.1 visibility modifiers are allowed for class constants
                    // for enumerations we are only interested in public once.
                    foreach ($reflection->getReflectionConstants() as $reflConstant) {
                        if ($reflConstant->isPublic()) {
                            $scopeConstants[ $reflConstant->getName() ] = $reflConstant->getValue();
                        }
                    }
                } else {
                    // In PHP < 7.1 all class constants were public by definition
                    $scopeConstants = $reflection->getConstants();
                }

                $constants = $scopeConstants + $constants;
            } while (($reflection = $reflection->getParentClass()) && $reflection->name !== __CLASS__);

            // Detect ambiguous values and report names
            $ambiguous = array();
            foreach ($constants as $value) {
                $names = array_keys($constants, $value, true);
                if (count($names) > 1) {
                    $ambiguous[var_export($value, true)] = $names;
                }
            }
            if (!empty($ambiguous)) {
                throw new LogicException(
                    'All possible values needs to be unique. The following are ambiguous: '
                    . implode(', ', array_map(function ($names) use ($constants) {
                        return implode('/', $names) . '=' . var_export($constants[$names[0]], true);
                    }, $ambiguous))
                );
            }

            self::$constants[$class] = $constants;
        }

        return self::$constants[$class];
    }

    /**
     * Get an enumerator instance by the given name.
     *
     * This will be called automatically on calling a method
     * with the same name of a defined enumerator.
     *
     * @param string $method The name of the enumeraotr (called as method)
     * @param array  $args   There should be no arguments
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous constant values
     */
    final public static function __callStatic($method, array $args)
    {
        return self::byName($method);
    }
}
