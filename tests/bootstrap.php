<?php

// report all errors
error_reporting(E_ALL);

// make sure zend.assertions are available (not disabled on compile time)
$zendassertions = ini_get('zend.assertions');
if (\PHP_VERSION_ID >= 70000 && $zendassertions == -1) {
    echo 'Please enable zend.assertions in php.ini (zend.assertions = 1)' . PHP_EOL
        . "Current ini setting: zend.assertions = {$zendassertions}]" . PHP_EOL;
    exit(1);
}

// activate assertions
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 0);
if (!class_exists('AssertionError')) {
    // AssertionError has been added in PHP-7.0
    class AssertionError extends Exception {};
}
assert_options(ASSERT_CALLBACK, function($file, $line, $code) {
    throw new AssertionError("assert(): Assertion '{$code}' failed in {$file} on line {$line}");
});

// installed itself
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';

// installed as dependency
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';

// not installed
} else {
    echo "php-enum not installed - please run 'composer install'" . PHP_EOL;
    exit(1);
}

// autload test files
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
