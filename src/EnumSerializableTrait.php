<?php

namespace MabeEnum;

use RuntimeException;
use LogicException;

/**
 * Trait to make enumerations serializable
 *
 * This trait is a workaround to make enumerations serializable.
 *
 * Please note that this feature breaks singleton behaviour of your enumerations
 * if an enumeration will be unserialized after it was instantiated already.
 *
 * @copyright 2019 Marc Bennewitz
 * @license http://github.com/marc-mabe/php-enum/blob/master/LICENSE.txt New BSD License
 * @link http://github.com/marc-mabe/php-enum for the canonical source repository
 * @link https://github.com/marc-mabe/php-enum/issues/52 for further information about this feature
 */
trait EnumSerializableTrait
{
    /**
     * The method will be defined by MabeEnum\Enum
     * @return null|bool|int|float|string
     */
    abstract public function getValue();

    /**
     * Serialized the value of the enumeration
     * This will be called automatically on `serialize()` if the enumeration implements the `Serializable` interface
     * @return string
     */
    public function serialize()
    {
        return \serialize($this->getValue());
    }

    /**
     * Unserializes a given serialized value and push it into the current instance
     * This will be called automatically on `unserialize()` if the enumeration implements the `Serializable` interface
     * @param string $serialized
     * @throws RuntimeException On an unknown or invalid value
     * @throws LogicException   On changing numeration value by calling this directly
     */
    public function unserialize($serialized)
    {
        $value     = \unserialize($serialized);
        $constants = self::getConstants();
        $name      = \array_search($value, $constants, true);
        if ($name === false) {
            $message = \is_scalar($value)
                ? 'Unknown value ' . \var_export($value, true)
                : 'Invalid value of type ' . (\is_object($value) ? \get_class($value) : \gettype($value));
            throw new RuntimeException($message);
        }

        $class      = static::class;
        $enumerator = $this;
        $closure    = function () use ($class, $name, $value, $enumerator) {
            if ($value !== null && $this->value !== null) {
                throw new LogicException('Do not call this directly - please use unserialize($enum) instead');
            }

            $this->value = $value;

            if (!isset(self::$instances[$class][$name])) {
                self::$instances[$class][$name] = $enumerator;
            }
        };
        $closure = $closure->bindTo($this, Enum::class);
        $closure();
    }
}
