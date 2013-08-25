# php-enum
[![Build Status](https://secure.travis-ci.org/marc-mabe/php-enum.png?branch=master)](http://travis-ci.org/marc-mabe/php-enum)
[![Coverage Status](https://coveralls.io/repos/marc-mabe/php-enum/badge.png?branch=master)](https://coveralls.io/r/marc-mabe/php-enum?branch=master)
[![Total Downloads](https://poser.pugx.org/marc-mabe/php-enum/downloads.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Latest Stable Version](https://poser.pugx.org/marc-mabe/php-enum/v/stable.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Latest Unstable Version](https://poser.pugx.org/marc-mabe/php-enum/v/unstable.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Dependency Status](https://www.versioneye.com/php/marc-mabe:php-enum/dev-master/badge.png)](https://www.versioneye.com/php/marc-mabe:php-enum/dev-master)

This is a native PHP implementation to add enumeration support to PHP 5.x.
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
* SplEnum hasn't strict comparison


# API

    MabeEnum_Enum
    {
        protected $value = null;
        public function __construct($value = null);
        final public function getConstants();
        final public function setValue($value);
        final public function getValue()
        final public function setName($name);
        final public function getName();
        final public function __toString(); // Alias of getValue()
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

    class UserStatusEnum extends MabeEnum_Enum
    {
        const INACTIVE = 0;
        const ACTIVE   = 1;
        const DELETED  = 2;

        // default value
        protected $value = self::INACTIVE;
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
                $this->status = new UserStatusEnum();
            }
            return $this->status;
        }
    }

    $user = new User();
    echo 'Default user status: ' . $user->getStatus() . '(' . $user->getStatus()->getValue() . ')' . PHP_EOL;
    $user->setStatus(new UserStatusEnum(UserStatusEnum::ACTIVE));
    echo 'Changed user status: ' . $user->getStatus() . '(' . $user->getStatus()->getValue() . ')' . PHP_EOL;

**PRINTS**

    Default user status: INACTIVE (0)
    Changed user status: ACTIVE (1)

* Validation will be already done on basic class ```MabeEnum_Enum```
* Using type-hint makes arguments save
* Human readable name of a value is simple accessable


# Install

## Composer

Add ```marc-mabe/php-enum``` to the project's composer.json dependencies and run
```php composer.phar install```

## GIT

```git clone git://github.com/marc-mabe/php-enum.git```

(The class ```MabeEnum_Enum``` will be located in ```src/Mabe/Enum.php```)

## ZIP / TAR

Download the last version from [Github](https://github.com/marc-mabe/php-enum/tags)
and extract it.

(The class ```MabeEnum_Enum``` will be located in ```src/Mabe/Enum.php```)


# Examples

## Define a default value:

This example defines the constant ```ONE``` with value ```1``` as default
value.

    class MyEnumWithDefaultValue extends MabeEnum_Enum
    {
        const ONE = 1;
        const TWO = 2;
        protected $value = 1;
    }

## Enum without a default value

Don't define a ```$value``` to not define a default value if none of your
constant values has ```NULL``` as value.

That's because ```$value``` was defined as ```NULL``` in the base class and
no constant assignable to the default value.

    class MyEnumWithoutDefaultValue extends MabeEnum_Enum
    {
        const ONE = 1;
        const TWO = 2;
    }

* No argument on constructor results in an InvalidArgumentException

## Constant with NULL as value

Because ```$value``` property is defined as ```NULL``` a constant with
```NULL```  as value gets the default value automatically.

    class MyEnumWithNullAsDefaultValue extends MabeEnum_Enum
    {
        const NONE = null;
        const ONE  = 1;
        const TWO  = 2;
    }

To disable this behavior simply define ```$value``` to a value not assignable
to a constant.

    class MyEnumWithoutNullAsDefaultValue extends MabeEnum_Enum
    {
        const NONE = null;
        const ONE  = 1;
        const TWO  = 2;
        protected $value = -1;
    }

## Inheritance

It's also possible to extend other enumerations.

    class MyEnum extends MabeEnum_Enum
    {
        const ONE = 1;
        const TWO = 2;
    }

    class EnumInheritance extends MyEnum
    {
        const INHERITACE = 'Inheritance';
    }


# New BSD License

The files in this archive are released under the New BSD License.
You can find a copy of this license in LICENSE.txt file.
