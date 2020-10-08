<?php

// report all errors
error_reporting(E_ALL);

// make sure zend.assertions are available (not disabled on compile time)
$zendassertions = ini_get('zend.assertions');
if ($zendassertions == -1) {
    echo 'Please enable zend.assertions in php.ini (zend.assertions = 1)' . PHP_EOL
        . "Current ini setting: zend.assertions = {$zendassertions}]" . PHP_EOL;
    exit(1);
}

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
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
