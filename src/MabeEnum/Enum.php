<?php

namespace MabeEnum;

use ReflectionClass;
use InvalidArgumentException;
use LogicException;
use BadMethodCallException;

/**
 * Class to implement enumerations for PHP 5 (without SplEnum)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
abstract class Enum
{
    /**
     * The selected value
     * @var null|scalar
     */
    private $value;

    /**
     * The ordinal number of the value
     * @var null|int
     */
    private $ordinal;

    /**
     * An array of available constants by class
     * @var array ["$class" => ["$const" => $value, ...], ...]
     */
    private static $constants = array();

    /**
     * Already instantiated enums
     * @param array ["$class.$value" => MabeEnum\Enum, ...]
     */
    private static $instances = array();

    /**
     * Constructor
     * 
     * @param scalar $value The value to select
     * @throws InvalidArgumentException On an unknwon or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final private function __construct($value)
    {
        // find and set the given value
        // set the defined value because of non strict comparison
        $constants = static::getConstants();
        $const     = array_search($value, $constants);
        if ($const === false) {
            throw new InvalidArgumentException("Unknown value '{$value}'");
        }
        $this->value = $constants[$const];
    }

    /**
     * Get the selected value
     * @return string
     * @see getValue()
     */
    final public function __toString()
    {
        return (string) $this->value;
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
     * @return mixed
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the current selected constant name
     * @return string
     */
    final public function getName()
    {
        return array_search($this->value, $this::getConstants(), true);
    }

    /**
     * Get the ordinal number of the selected value
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
        foreach ($this::getConstants() as $constValue) {
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
     * @param scalar $value
     * @return Enum
     * @throws InvalidArgumentException On an unknwon or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    final static public function get($value)
    {
        $class = get_called_class();
        $id    = $class . '.' . $value;
        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        $instance = new $class($value);
        self::$instances[$id] = $instance;
        return $instance;
    }

    /**
     * Get an enum by the given name
     *
     * @param string $name The name to instantiate the enum by
     * @return Enum
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous constant values
     */
    final public static function getByName($name)
    {
        $classConst = 'static::' . $name;
        if (!defined($classConst)) {
            $class = get_called_class();
            throw new InvalidArgumentException($class . '::' . $name . ' not defined');
        }
        return static::get(constant($classConst));
    }

    /**
     * Get an enum by the given ordinal number
     *
     * @param int $ordinal The ordinal number to instantiate the enum by
     * @return Enum
     * @throws InvalidArgumentException On an invalid ordinal number
     * @throws LogicException           On ambiguous constant values
     */
    final public static function getByOrdinal($ordinal)
    {
        $constants = static::getConstants();
        $item      = array_slice($constants, $ordinal, 1, false);
        if (!$item) {
            throw new InvalidArgumentException(sprintf(
                'Invalid ordinal number, must between 0 and %s',
                count($constants) - 1
            ));
        }
        return static::get(current($item));
    }

    /**
     * Clears all instantiated enums
     *
     * NOTE: This can break singleton behavior ... use it with caution!
     *
     * @param null|string $class
     * @return void
     */
    final static public function clear()
    {
        $class  = get_called_class();

        // clear instantiated enums
        $prefix       = $class . '.';
        $prefixLength = strlen($prefix);
        foreach (self::$instances as $id => $enum) {
            if (strncasecmp($prefix, $id, $prefixLength) === 0) {
                unset(self::$instances[$id]);
            }
        }

        // clear constants buffer
        foreach (self::$constants as $constantsClass => & $constants) {
            if (strcasecmp($class, $constantsClass) === 0) {
                unset(self::$constants[$constantsClass]);
                break;
            }
        }
    }

    /**
     * Get all available constants
     * @return array
     * @throws LogicException On ambiguous constant values
     */
    final static public function getConstants()
    {
        $class = get_called_class();
        if (isset(self::$constants[$class])) {
            return self::$constants[$class];
        }

        $reflection = new ReflectionClass($class);
        $constants  = $reflection->getConstants();

        // Constant values needs to be unique
        if (max(array_count_values($constants)) > 1) {
            $ambiguous = array_map(function ($v) use ($constants) {
                return implode('/', array_keys($constants, $v)) . '=' . $v;
            }, array_unique(array_diff_assoc($constants, array_unique($constants))));
            throw new LogicException(sprintf(
                'All possible values needs to be unique. The following are ambiguous: %s',
                implode(', ', $ambiguous)
            ));
        }

        // This is required to make sure that constants of base classes will be the first
        while ( ($reflection = $reflection->getParentClass()) ) {
            $constants = $reflection->getConstants() + $constants;
        }

        self::$constants[$class] = $constants;
        return $constants;
    }

    /**
     * Instantiate an enum by a name of a constant.
     *
     * This will be called automatically on calling a method
     * with the same name of a defined constant.
     *
     * @param string $method The name to instantiate the enum by (called as method)
     * @param array  $args   There should be no arguments
     * @return Enum
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous constant values
     */
    final public static function __callStatic($method, array $args)
    {
        return static::getByName($method);
    }
}
