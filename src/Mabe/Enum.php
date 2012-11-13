<?php
/**
* Class to implement enumerations for PHP 5 (without SplEnum) 
* 
* @link http://github.com/marc-mabe/php-enum for the canonical source repository
* @copyright Copyright (c) 2012 Marc Bennewitz
* @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
*/

abstract class Mabe_Enum
{
    /**
     * The current selected value
     * @var mixed
     */
    protected $value = null;
    
    /**
     * An array of available constants
     * @var array
     */
    private $constants = null;

    /**
     * Constructor
     * 
     * @param mixed $value The value to select
     * @throws InvalidArgumentException
     */
    final public function __construct($value = null)
    {
        $reflectionClass = new \ReflectionClass($this);
        $this->constants = $reflectionClass->getConstants();
       
        if (func_num_args() > 0) {
            $this->setValue($value);
        } elseif (!in_array($this->value, $this->constants, true)) {
            throw new InvalidArgumentException("No value given and no default value defined");
        }
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
     * Select a new value
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    final public function setValue($value)
    {
        if (!in_array($value, $this->constants, true)) {
            throw new InvalidArgumentException("Unknown value '{$value}'");
        }
        $this->value = $value;
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
     * Select a new value by constant name
     * @param string $name
     * @throws InvalidArgumentException
     */
    final public function setName($name)
    {
        if (!array_key_exists($name, $this->constants)) {
            throw new InvalidArgumentException("Unknown name '{$name}'");
        }
        $this->value = $this->constants[$name];
    }

    /**
     * Get the current selected constant name
     * @return string
     */
    final public function getName()
    {
        return array_search($this->value, $this->constants, true);
    }

    /**
     * Get the current selected constant name
     * @return string
     * @see getName()
     */
    final public function __toString()
    {
        return $this->getName();
    }
    
    /**
     * Get the current selected value
     * @return mixed
     * @see getValue()
     */
    final public function __invoke()
    {
        return $this->getValue();
    }
}
