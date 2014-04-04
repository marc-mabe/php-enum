<?php

namespace MabeEnum;

use ReflectionClass;
use InvalidArgumentException;
use LogicException;

/**
 * Class to implement enumerations for PHP 5 (without SplEnum)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2013 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
abstract class Enum
{
    /**
     * The selected value
     *
     * @var null|boolean|int|float|string
     */
    private $value;

    /**
     * The ordinal number of the value
     *
     * @var null|int
     */
    private $ordinal;

    /**
     * An array of available constants by class
     *
     * @var array ["$class" => ["$const" => $value, ...], ...]
     */
    private static $constants = array();

    /**
     * Already instantiated enums
     *
     * @var array ["$class" => ["$const" => $instance, ...], ...]
     */
    private static $instances = array();

    /**
     * Constructor
     *
     * @param null|boolean|int|float|string $value   The value to select
     * @param int|null                      $ordinal The ordinal number of the value
     */
    final private function __construct($value, $ordinal = null)
    {
        $this->value   = $value;
        $this->ordinal = $ordinal;
    }

    /**
     * Get the current selected constant name
     *
     * @return string
     * @see getName()
     */
    public function __toString()
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
     * Get the current selected value
     *
     * @return null|boolean|int|float|string
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the current selected constant name
     *
     * @return string
     */
    final public function getName()
    {
        return array_search($this->value, self::detectConstants(get_called_class()), true);
    }

    /**
     * Get the ordinal number of the selected value
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
     * Get an enum of the given value
     *
     * @param static|null|boolean|int|float|string $value
     * @return static
     * @throws InvalidArgumentException On an unknwon or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final public static function get($value)
    {
        if ($value instanceof static) {
            $value = $value->getValue();
        }

        $class     = get_called_class();
        $constants = self::detectConstants($class);
        $name      = array_search($value, $constants, true);
        if ($name === false) {
            if (is_scalar($value)) {
                throw new InvalidArgumentException('Unknown value ' . var_export($value, true));
            } else {
                throw new InvalidArgumentException('Invalid value of type ' . gettype($value));
            }
        }

        if (isset(self::$instances[$class][$name])) {
            return self::$instances[$class][$name];
        }

        return self::$instances[$class][$name] = new $class($constants[$name]);
    }

    /**
     * Get an enum by the given name
     *
     * @param string $name The name to instantiate the enum by
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous constant values
     */
    final public static function getByName($name)
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
     * Get an enum by the given ordinal number
     *
     * @param int $ordinal The ordinal number to instantiate the enum by
     * @return static
     * @throws InvalidArgumentException On an invalid ordinal number
     * @throws LogicException           On ambiguous constant values
     */
    final public static function getByOrdinal($ordinal)
    {
        $ordinal   = (int) $ordinal;
        $class     = get_called_class();
        $constants = self::detectConstants($class);
        $item      = array_slice($constants, $ordinal, 1, false);
        if (!$item) {
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
     * Clears all instantiated enums
     *
     * NOTE: This can break singleton behavior ... use it with caution!
     *
     * @return void
     */
    final public static function clear()
    {
        $class = get_called_class();
        unset(self::$instances[$class], self::$constants[$class]);
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
     * Detect constants available by given class
     *
     * @param string $class
     * @return array
     * @throws LogicException On ambiguous constant values
     */
    private static function detectConstants($class)
    {
        if (!isset(self::$constants[$class])) {
            $reflection = new ReflectionClass($class);
            $constants  = $reflection->getConstants();

            // values needs to be unique
            $ambiguous = array();
            foreach ($constants as $value) {
                $names = array_keys($constants, $value, true);
                if (count($names) > 1) {
                    $ambiguous[var_export($value, true)] = $names;
                }
            }
            if ($ambiguous) {
                throw new LogicException(
                    'All possible values needs to be unique. The following are ambiguous: '
                    . implode(', ', array_map(function ($names) use ($constants) {
                        return implode('/', $names) . '=' . var_export($constants[$names[0]], true);
                    }, $ambiguous))
                );
            }

            // This is required to make sure that constants of base classes will be the first
            while (($reflection = $reflection->getParentClass()) && $reflection->name != __CLASS__) {
                $constants = $reflection->getConstants() + $constants;
            }

            self::$constants[$class] = $constants;
        }

        return self::$constants[$class];
    }

    /**
     * Instantiate an enum by a name of a constant.
     *
     * This will be called automatically on calling a method
     * with the same name of a defined constant.
     *
     * @param string $method The name to instantiate the enum by (called as method)
     * @param array  $args   There should be no arguments
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous constant values
     */
    final public static function __callStatic($method, array $args)
    {
        return self::getByName($method);
    }
}
