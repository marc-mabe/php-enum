<?php

/**
 * Class to implement enumerations for PHP 5 (without SplEnum)
 *
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @copyright Copyright (c) 2012 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 */
abstract class MabeEnum_Enum
{
    /**
     * The current selected value
     * @var null|scalar
     */
    private $value;

    /**
     * The ordinal number of the value
     * @var null|int
     */
    private $ordinal;

    /**
     * An array of available constants
     * @var null|array
     */
    private $constants;

    /**
     * Constructor
     * 
     * @param scalar $value The value to select
     * @throws InvalidArgumentException
     */
    public function __construct($value)
    {
        $reflectionClass = new ReflectionClass($this);
        $constants       = $reflectionClass->getConstants();

        // Constant values needs to be unique
        if (count($constants) > count(array_unique($constants))) {
            $ambiguous = array();
            foreach (array_count_values($constants) as $constValue => $countValue) {
                if ($countValue < 2) {
                    continue;
                }
                $ambiguous[] = $constValue;
            }
            throw new LogicException(sprintf(
                'All possible values needs to be unique. The following are ambiguous: %s',
                "'" . implode("', '", $ambiguous) . "'"
            ));
        }

        // This is required to make sure that constants of base classes will be the first
        while ( ($reflectionClass = $reflectionClass->getParentClass()) ) {
            $constants = $reflectionClass->getConstants() + $constants;
        }
        $this->constants = $constants;

        // find and set the given value
        // set the defined value because of non strict comparison
        $const = array_search($value, $this->constants);
        if ($const === false) {
            throw new InvalidArgumentException("Unknown value '{$value}'");
        }
        $this->value = $this->constants[$const];
    }

    /**
     * Get all available constants
     * @return array
     */
    final public function getConstants()
    {
        return $this->constants;
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
        return array_search($this->value, $this->constants, true);
    }

    final public function getOrdinal()
    {
        if ($this->ordinal !== null) {
            return $this->ordinal;
        }

        // detect ordinal
        $ordinal = 0;
        $value   = $this->value;
        foreach ($this->constants as $constValue) {
            if ($value === $constValue) {
                break;
            }
            ++$ordinal;
        }

        $this->ordinal = $ordinal;
        return $ordinal;
    }

    /**
     * Get the current selected constant name
     * @return string
     * @see getName()
     */
    final public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Instantiate an enum by a name of a constant.
     *
     * This will be called automatically on calling a method
     * with the same name of a defined constant.
     *
     * NOTE: THIS WORKS FOR PHP >= 5.3 ONLY
     *
     * @param string $const The name of the constant to instantiate the enum with
     * @param array  $args  There should be no arguments
     * @throws BadMethodCallException
     */
    final public static function __callStatic($const, array $args)
    {
        $class      = get_called_class();
        $classConst = $class . '::' . $const;
        if (!defined($classConst)) {
            throw new BadMethodCallException($classConst . ' not defined');
        }
        return new $class(constant($classConst));
    }
}
