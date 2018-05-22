# php-enum
[![Build Status](https://secure.travis-ci.org/marc-mabe/php-enum.png?branch=master)](http://travis-ci.org/marc-mabe/php-enum)
[![Quality Score](https://scrutinizer-ci.com/g/marc-mabe/php-enum/badges/quality-score.png?s=7dfddb19a12314ecc5f05eeb2b297bdde3ad2623)](https://scrutinizer-ci.com/g/marc-mabe/php-enum/)
[![Code Coverage](https://scrutinizer-ci.com/g/marc-mabe/php-enum/badges/coverage.png?s=8442d532fad964fd3d8afe493ac2d0d65162306a)](https://scrutinizer-ci.com/g/marc-mabe/php-enum/)
[![Total Downloads](https://poser.pugx.org/marc-mabe/php-enum/downloads.png)](https://packagist.org/packages/marc-mabe/php-enum)
[![Latest Stable](https://poser.pugx.org/marc-mabe/php-enum/v/stable.png)](https://packagist.org/packages/marc-mabe/php-enum)

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

## PHPDoc

You can find auto-generated PHP documentation in the [wiki](https://github.com/marc-mabe/php-enum/wiki).

## Basics

```php
use MabeEnum\Enum;

// define an own enumeration class
class UserStatus extends Enum
{
    const INACTIVE = 'i';
    const ACTIVE   = 'a';
    const DELETED  = 'd';

    // all scalar data types and arrays are supported as enumerator values
    const NIL     = null;
    const BOOLEAN = true;
    const INT     = 1234;
    const STR     = 'string';
    const FLOAT   = 0.123;
    const ARR     = ['this', 'is', ['an', 'array']];

    // Enumerators will be generated from public constants only
    public    const PUBLIC_CONST    = 'public constant';    // this will be an enumerator
    protected const PROTECTED_CONST = 'protected constant'; // this will NOT be an enumerator
    private   const PRIVATE_CONST   = 'private constant';   // this will NOT be an enumerator

    // works since PHP-7.0 - see https://wiki.php.net/rfc/context_sensitive_lexer
    const TRUE      = 'true';
    const FALSE     = 'false';
    const NULL      = 'null';
    const PUBLIC    = 'public';
    const PRIVATE   = 'private';
    const PROTECTED = 'protected';
    const FUNCTION  = 'function';
    const TRAIT     = 'trait';
    const INTERFACE = 'interface';

    // Doesn't work - see https://wiki.php.net/rfc/class_name_scalars
    // const CLASS = 'class';
}

// ways to instantiate an enumerator
$status = UserStatus::get(UserStatus::ACTIVE); // by value or instance
$status = UserStatus::ACTIVE();                // by name as callable
$status = UserStatus::byValue('a');            // by value
$status = UserStatus::byName('ACTIVE');        // by name
$status = UserStatus::byOrdinal(1);            // by ordinal number

// basic methods of an instantiated enumerator
$status->getValue();   // returns the selected constant value
$status->getName();    // returns the selected constant name
$status->getOrdinal(); // returns the ordinal number of the selected constant

// basic methods to list defined enumerators
UserStatus::getEnumerators();  // returns a list of enumerator instances
UserStatus::getValues();       // returns a list of enumerator values
UserStatus::getNames();        // returns a list of enumerator names
UserStatus::getOrdinals();     // returns a list of ordinal numbers
UserStatus::getConstants();    // returns an associative array of enumerator names to enumerator values

// same enumerators (of the same enumeration class) holds the same instance
UserStatus::get(UserStatus::ACTIVE) === UserStatus::ACTIVE()
UserStatus::get(UserStatus::DELETED) != UserStatus::INACTIVE()

// simplified way to compare two enumerators
$status = UserStatus::ACTIVE();
$status->is(UserStatus::ACTIVE);     // true
$status->is(UserStatus::ACTIVE());   // true
$status->is(UserStatus::DELETED);    // false
$status->is(UserStatus::DELETED());  // false
```

## Type-Hint

```php
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
            // initialize default
            $this->status = UserStatus::INACTIVE();
        }
        return $this->status;
    }
}
```

### Type-Hint issue

Because in normal OOP the above example allows `UserStatus` and types inherited from it.

Please think about the following example:

```php
class ExtendedUserStatus extends UserStatus
{
    const EXTENDED = 'extended';
}

$user = new User();
$user->setStatus(ExtendedUserStatus::EXTENDED());
```

Now the setter receives a status it doesn't know about but allows it.

#### Solution 1: Finilize the enumeration

```php
final class UserStatus extends Enum
{
    // ...
}

class User
{
    protected $status;

    public function setStatus(UserStatus $status)
    {
        $this->status = $status;
    }
}
````

* Nice and obvious solution

* Resulting behaviour matches native enumeration implementation of most other languages (like Java)

But as this library emulates enumerations it has a view downsides:

* Enumerator values can not be used directly
  * `$user->setStatus(UserStatus::ACTIVE)` fails
  * `$user->setStatus(UserStatus::ACTIVE())` works

* Does not help if the enumeration was defined in an external library


#### Solution 2: Using `Enum::get()`

```php
class User
{
    public function setStatus($status)
    {
        $this->status = UserStatus::get($status);
    }
}
```

* Makes sure the resulting enumerator exactly matches an enumeration. (Inherited enumerators as not allowed).

* Allows enumerator values directly
  * `$user->setStatus(UserStatus::ACTIVE)` works
  * `$user->setStatus(UserStatus::ACTIVE())` works

* Also works for enumerations defined in external libraries

But of course this solution has downsides, too:

* Looses declarative type-hint

* A bit slower


## EnumSet

An `EnumSet` groups enumerators of the same enumeration type together.

It implements `Iterator` and `Countable`
so elements can be iterated and counted like a normal array
using `foreach` and `count()`.

Internally it's based on a bitset. Integer bitset or binary bitset
depending on how many enumerators are defined for the given enumeration.

Enumerators attached to an `EnumSet` are unique and ordered based on it's ordinal number by design.

```php
use MabeEnum\EnumSet;

// create a new EnumSet
$enumSet = new EnumSet('UserStatus');


// attach enumerators (by value or by instance)
$enumSet->attach(UserStatus::INACTIVE);
$enumSet->attach(UserStatus::ACTIVE());
$enumSet->attach(UserStatus::DELETED());


// detach enumerators (by value or by instance)
$enumSet->detach(UserStatus::INACTIVE);
$enumSet->detach(UserStatus::DELETED());


// contains enumerators (by value or by instance)
$enumSet->contains(UserStatus::INACTIVE); // bool


// count number of attached enumerations
$enumSet->count();
count($enumSet);


// convert to array
$enumSet->getValues();      // List of enumerator values
$enumSet->getEnumerators(); // List of enumerator instances
$enumSet->getNames();       // List of enumerator names
$enumSet->getOrdinals();    // List of ordinal numbers


// iterating over the set
foreach ($enumSet as $ordinal => $enum) {
    gettype($ordinal);  // int (the ordinal number of the enumerator)
    get_class($enum);   // UserStatus (enumerator object)
}


// compare two EnumSets
$enumSet->isEqual($other);    // Check if the EnumSet is the same as other
$enumSet->isSubset($other);   // Check if the EnumSet is a subset of other
$enumSet->isSuperset($other); // Check if the EnumSet is a superset of other

$enumSet->union($other);     // Produce a new set with enumerators from both this and other (this | other)
$enumSet->intersect($other); // Produce a new set with enumerators common to both this and other (this & other)
$enumSet->diff($other);      // Produce a new set with enumerators in this but not in other (this - other)
$enumSet->symDiff($other);   // Produce a new set with enumerators in either this and other but not in both (this ^ other)
```

## EnumMap

An `EnumMap` maps enumerators of the same type to data assigned to.

It implements `ArrayAccess`, `Countable` and `SeekableIterator`
so elements can be accessed, iterated and counted like a normal array
using `$enumMap[$key]`, `foreach` and `count()`.

```php
use MabeEnum\EnumMap;

// create a new EnumMap
$enumMap = new EnumMap('UserStatus');


// read and write key-value-pairs like an array
$enumMap[UserStatus::INACTIVE] = 'inaktiv';
$enumMap[UserStatus::ACTIVE]   = 'aktiv';
$enumMap[UserStatus::DELETED]  = 'gelöscht';
$enumMap[UserStatus::INACTIVE]; // 'inaktiv';
$enumMap[UserStatus::ACTIVE];   // 'aktiv';
$enumMap[UserStatus::DELETED];  // 'gelöscht';

isset($enumMap[UserStatus::DELETED]); // true
unset($enumMap[UserStatus::DELETED]);
isset($enumMap[UserStatus::DELETED]); // false

// ... no matter if you use enumerator values or enumerator objects
$enumMap[UserStatus::INACTIVE()] = 'inaktiv';
$enumMap[UserStatus::ACTIVE()]   = 'aktiv';
$enumMap[UserStatus::DELETED()]  = 'gelöscht';
$enumMap[UserStatus::INACTIVE()]; // 'inaktiv';
$enumMap[UserStatus::ACTIVE()];   // 'aktiv';
$enumMap[UserStatus::DELETED()];  // 'gelöscht';

isset($enumMap[UserStatus::DELETED()]); // true
unset($enumMap[UserStatus::DELETED()]);
isset($enumMap[UserStatus::DELETED()]); // false


// count number of attached elements
$enumMap->count();
count($enumMap);


// support for null aware exists check
$enumMap[UserStatus::NULL] = null;
isset($enumMap[UserStatus::NULL]);    // false
$enumMap->contains(UserStatus::NULL); // true


// iterating over the map
foreach ($enumMap as $enum => $value) {
    get_class($enum);  // UserStatus (enumerator object)
    gettype($value);   // string (the value the enumerators maps to)
}

// get a list of keys (= a list of enumerator objects)
$enumMap->getKeys();

// get a list of values (= a list of values the enumerator maps to)
$enumMap->getValues();
```

## Serializing

Because this enumeration implementation is based on a singleton pattern and in PHP
it's currently impossible to unserialize a singleton without creating a new instance
this feature isn't supported without any additional work.

As of it's an often requested feature there is a trait that can be added to your
enumeration definition. The trait adds serialization functionallity and injects
the unserialized enumeration instance in case it's the first one.
This reduces singleton behavior breakage but still it beaks if it's not the first
instance and you could result in two different instance of the same enumeration.

**Use it with caution!**

PS: `EnumSet` and `EnumMap` are serializable by default as long as you don't set other non-serializable values.


### Example of using EnumSerializableTrait

```php
use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use Serializable;

class CardinalDirection extends Enum implements Serializable
{
    use EnumSerializableTrait;

    const NORTH = 'n';
    const EAST  = 'e';
    const WEST  = 'w';
    const SOUTH = 's';
}

$north1 = CardinalDirection::NORTH();
$north2 = unserialize(serialize($north1));

var_dump($north1 === $north2);  // returns FALSE as described above
var_dump($north1->is($north2)); // returns TRUE - this way the two instances are treated equal
var_dump($north2->is($north1)); // returns TRUE - equality works in both directions
```

# Why not `SplEnum`

* `SplEnum` is not build-in into PHP and requires pecl extension installed.
* Instances of the same value of an `SplEnum` are not the same instance.
* No support for `EnumMap` or `EnumSet`.


# Install

## Composer

Add `marc-mabe/php-enum` to the project's composer.json dependencies and run
`php composer.phar install`

## GIT

`git clone git://github.com/marc-mabe/php-enum.git`

## ZIP / TAR

Download the last version from [Github](https://github.com/marc-mabe/php-enum/tags)
and extract it.


# New BSD License

The files in this archive are released under the New BSD License.
You can find a copy of this license in LICENSE.txt file.
