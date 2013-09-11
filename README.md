# php-enum
[![Build Status](https://secure.travis-ci.org/marc-mabe/php-enum.png?branch=master)](http://travis-ci.org/marc-mabe/php-enum)
[![Coverage Status](https://coveralls.io/repos/marc-mabe/php-enum/badge.png?branch=master)](https://coveralls.io/r/marc-mabe/php-enum?branch=master)
[![Total Downloads](https://poser.pugx.org/marc-mabe/php-enum/downloads.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Latest Stable Version](https://poser.pugx.org/marc-mabe/php-enum/v/stable.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Latest Unstable Version](https://poser.pugx.org/marc-mabe/php-enum/v/unstable.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Dependency Status](https://www.versioneye.com/php/marc-mabe:php-enum/dev-master/badge.png)](https://www.versioneye.com/php/marc-mabe:php-enum/dev-master)

This is a native PHP implementation to add enumeration support to PHP >= 5.3
It's an abstract class that needs to be extended to use it.


# What is an Enumeration?

[Wikipedia](http://wikipedia.org/wiki/Enumerated_type)
> In computer programming, an enumerated type (also called enumeration or enum)
> is a data type consisting of a set of named values called elements, members
> or enumerators of the type. The enumerator names are usually identifiers that
> behave as constants in the language. A variable that has been declared as
> having an enumerated type can be assigned any of the enumerators as a value.
> In other words, an enumerated type has values that are different from each
> other, and that can be compared and assigned, but which do not have any
> particular concrete representation in the computer's memory; compilers and
> interpreters can represent them arbitrarily.


# Why not ```SplEnum```

* It's not build-in PHP and requires pecl extension
* SplEnum is too much magic under the hod


# API

    abstract class MabeEnum\Enum
    {
        /**
         * Constructor
         * @param scalar $value             The enum value to select
         * @throws InvalidArgumentException On unknwon value
         */
        public function __construct($value);
    
        /**
         * Returns an assoc array of defined constant names and the values
         * @return array
         */
        final public function getConstants();
    
        /**
         * Get the selected value
         * @return scalar
         */
        final public function getValue();
    
        /**
         * Get the constant name of the selected value
         * @return string
         */
        final public function getName();
    
        /**
         * Get the ordinal number of the selected value
         * @return int
         */
        final public function getOrdinal();
    
        /**
         * Get the selected value as string
         * (This will be called automatically on converting into a string)
         * @return string
         */
        final public function __toString();
    
        /**
         * Instantiate a new enum were the selected value
         * is the constant name of the called method name
         * (This will be called automatically on calling static method)
         * NOTE: THIS WILL WORK FOR PHP >= 5.3 ONLY
         * @param string $name            The name of the constant to instantiate
         * @param array  $args            This should be an empty array (no arguments)
         * @throws BadMethodCallException If the called method hasn't the same name as a constant
         * @return MabeEnum\Enum          The instantiated enum
         */
        final public static __callStatic($name, array $args);
    }


# Usage

## The way of class constants

    class User
    {
        const INACTIVE = 0;
        const ACTIVE   = 1;
        const DELETED  = 2;

        protected $status = 0;

        public function setStatus($status)
        {
            $intStatus = (int)$status;
            if (!in_array($intStatus, array(self::INACTIVE, self::ACTIVE, self::DELETED))) {
                throw new InvalidArgumentException("Invalid status {$status}");
            }
            $this->status = $intStatus;
        }

        public function getStatus()
        {
            return $this->status;
        }
    }

    $user = new User();
    echo 'Default user status: ' . $user->getStatus() . PHP_EOL;
    $user->setStatus(User::ACTIVE);
    echo 'Changed user status: ' . $user->getStatus() . PHP_EOL;

**PRINTS**

    Default user status: 0
    Changed user status: 1

* Requires validation on every use
* Hard to extend the list of possible values
* Hard to get a human readable name of a value

## The way of php-enum:

    use MabeEnum\Enum;
    
    class UserStatusEnum extends Enum
    {
        const INACTIVE = 0;
        const ACTIVE   = 1;
        const DELETED  = 2;
    }
    
    class User
    {
        protected $status;
    
        public function setStatus(UserStatusEnum $status)
        {
            $this->status = $status;
        }
    
        public function getStatus()
        {
            if (!$this->status) {
                // init default status
                $this->status = UserStatusEnum::INACTIVE();
            }
            return $this->status;
        }
    }
    
    $user = new User();
    echo 'Default user status: ' . $user->getStatus() . '(' . $user->getStatus()->getValue() . ')' . PHP_EOL;
    $user->setStatus(UserStatusEnum::ACTIVE());
    echo 'Changed user status: ' . $user->getStatus() . '(' . $user->getStatus()->getValue() . ')' . PHP_EOL;

**PRINTS**

    Default user status: INACTIVE (0)
    Changed user status: ACTIVE (1)

* Validation will be already done on basic class ```MabeEnum\Enum```
* Using type-hint makes arguments save
* Human readable name of a value is simple accessable


# Install

## Composer

Add ```marc-mabe/php-enum``` to the project's composer.json dependencies and run
```php composer.phar install```

## GIT

```git clone git://github.com/marc-mabe/php-enum.git```

## ZIP / TAR

Download the last version from [Github](https://github.com/marc-mabe/php-enum/tags)
and extract it.


# Examples

## Define a default value:

This example defines the constant ```ONE``` with value ```1``` as default
value.

    use MabeEnum\Enum;
    
    class MyEnumWithDefaultValue extends Enum
    {
        const ONE = 1;
        const TWO = 2;
    
        public function __construct($value = self::ONE)
        {
            parent::__construct($value);
        }
    }

## Inheritance

It's also possible to extend other enumerations.

    use MabeEnum\Enum;
    
    class MyEnum extends Enum
    {
        const ONE = 1;
        const TWO = 2;
    }
    
    class EnumInheritance extends MyEnum
    {
        const INHERITANCE = 'Inheritance';
    }

## Simplified instantiation

It's possible to call one of the defined constants like a method
and you will get the instantiated enum as a result.

    use MabeEnum\Enum;
    
    class MyEnum extends Enum
    {
        const ONE = 1;
        const TWO = 2;
    }
    
    $enum = MyEnum::ONE();

# New BSD License

The files in this archive are released under the New BSD License.
You can find a copy of this license in LICENSE.txt file.
