# php-enum
[![Build Status](https://secure.travis-ci.org/marc-mabe/php-enum.png?branch=master)](http://travis-ci.org/marc-mabe/php-enum)
[![Quality Score](https://scrutinizer-ci.com/g/marc-mabe/php-enum/badges/quality-score.png?s=7dfddb19a12314ecc5f05eeb2b297bdde3ad2623)](https://scrutinizer-ci.com/g/marc-mabe/php-enum/)
[![Code Coverage](https://scrutinizer-ci.com/g/marc-mabe/php-enum/badges/coverage.png?s=8442d532fad964fd3d8afe493ac2d0d65162306a)](https://scrutinizer-ci.com/g/marc-mabe/php-enum/)
[![Total Downloads](https://poser.pugx.org/marc-mabe/php-enum/downloads.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Latest Stable](https://poser.pugx.org/marc-mabe/php-enum/v/stable.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Dependency Status](https://www.versioneye.com/php/marc-mabe:php-enum/dev-master/badge.png)](https://www.versioneye.com/php/marc-mabe:php-enum/dev-master)

This is a native PHP implementation to add enumeration support to PHP >= 5.3.
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


# Usage

## Basics

    use MabeEnum\Enum;

    // define an own enumeration class
    class UserStatus extends Enum
    {
        const INACTIVE = 0;
        const ACTIVE   = 1;
        const DELETED  = 2;

        // all scalar datatypes are supported
        const NIL     = null;
        const BOOLEAN = true;
        const INT     = 1234;
        const STR     = 'string';
        const FLOAT   = 0.123;
    }
    
    // different ways to instantiate an enumeration
    $status = UserStatus::get(UserStatus::ACTIVE);
    $status = UserStatus::ACTIVE();
    $status = UserStatus::getByName('ACTIVE');
    $status = UserStatus::getByOrdinal(1);
    
    // available methods to get the selected entry
    $status->getValue();   // returns the selected constant value
    $status->getName();    // returns the selected constant name
    $status->getOrdinal(); // returns the ordinal number of the selected constant
    (string) $status;      // returns the selected constant name
    
    // same enumerations of the same class holds the same instance
    UserStatus::get(UserStatus::ACTIVE) === UserStatus::ACTIVE()
    UserStatus::get(UserStatus::DELETED) != UserStatus::INACTIVE()

    // simplified way to compare two enumerations
    UserStatus::ACTIVE()->is(UserStatus::ACTIVE);     // true
    UserStatus::ACTIVE()->is(UserStatus::ACTIVE());   // true
    UserStatus::ACTIVE()->is(UserStatus::DELETED);    // false
    UserStatus::ACTIVE()->is(UserStatus::DELETED());  // false


## Type-Hint
    
    use MabeEnum\Enum;

    class User
    {
        protected $status;
    
        public function setStatus(UserStatus $status)
        {
            $this->status = $status;
        }
    
        public function getStatus()
        {
            if (!$this->status) {
                // initialize a default value
                $this->status = UserStatus::get(UserStatus::INACTIVE);
            }
            return $this->status;
        }
    }

### Type-Hint issue

Because in normal OOP the above example allows `UserStatus` and types inherited from it.

Please think about the following example:

    class ExtendedUserStatus
    {
        const EXTENDED = 'extended';
    }

    $user->setStatus(ExtendedUserStatus::EXTENDED());

Now the setter receives a status it doesn't know about but allows it.
If your `User` class doesn't allow it the following is the recommanded way:

    class User
    {
        // ...
        public function setStatus($status)
        {
            $this->status = UserStatus::get($status);
        }
        // ...
    }

Now you are 100% sure to work with an exact instace of `UserStatus`.

(If the setter receives an extended status the value will be used to receive the
corresponding instance of `UserStatus` else an exception will be thrown.)

## EnumMap

An ```EnumMap``` maps enumeration instances of exactly one type to data assigned to.
Internally the ```EnumMap``` is based of ```SplObjectStorage```.

    use MabeEnum\EnumMap;

    // create a new EnumMap
    $enumMap = new EnumMap('UserStatus');

    // attach entries (by value or by instance)
    $enumMap->attach(UserStatus::INACTIVE, 'inaktiv');
    $enumMap->attach(UserStatus::ACTIVE(), 'aktiv');
    $enumMap->attach(UserStatus::DELETED(), 'gelöscht');
    
    // detach entries (by value or by instance)
    $enumMap->detach(UserStatus::INACTIVE);
    $enumMap->detach(UserStatus::DELETED());
    
    // iterate
    var_dump(iterator_to_array($enumSet)); // array(0 => UserStatus{$value=1});

    // define key and value used for iteration
    $enumSet->setFlags(EnumSet::KEY_AS_NAME | EnumSet::CURRENT_AS_DATA);
    var_dump(iterator_to_array($enumSet)); // array('ACTIVE' => 'aktiv');


## EnumSet

An ```EnumSet``` groups enumeration instances of exactly one type together.
Internally it's based of a list (array) of ordinal values.

    use MabeEnum\EnumSet;

    // create a new EnumSet
    $enumSet = new EnumSet('UserStatus');

    // attach entries (by value or by instance)
    $enumSet->attach(UserStatus::INACTIVE);
    $enumSet->attach(UserStatus::ACTIVE());
    $enumSet->attach(UserStatus::DELETED());
    
    // detach entries (by value or by instance)
    $enumSet->detach(UserStatus::INACTIVE);
    $enumSet->detach(UserStatus::DELETED());
    
    // iterate
    var_dump(iterator_to_array($enumSet)); // array(0 => UserStatus{$value=1});


# Why not ```SplEnum```

* ```SplEnum``` is not build-in into PHP and requires pecl extension installed.
* Instances of the same value of an ```SplEnum``` are not the same instance.
* ```SplEnum``` doesn't have implemented ```EnumMap``` or ```EnumSet```.


# Install

## Composer

Add ```marc-mabe/php-enum``` to the project's composer.json dependencies and run
```php composer.phar install```

## GIT

```git clone git://github.com/marc-mabe/php-enum.git```

## ZIP / TAR

Download the last version from [Github](https://github.com/marc-mabe/php-enum/tags)
and extract it.


# New BSD License

The files in this archive are released under the New BSD License.
You can find a copy of this license in LICENSE.txt file.
