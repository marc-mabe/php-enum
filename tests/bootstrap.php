<?php

ini_set('error_reporting', E_ALL);

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
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
