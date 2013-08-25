<?php

ini_set('error_reporting', E_ALL);

// init PHPUnit
require_once 'PHPUnit/Framework/TestCase.php';
if ('@package_version@' !== PHPUnit_Runner_Version::id() && version_compare(PHPUnit_Runner_Version::id(), '3.6.0', '<')) {
    echo 'This version of PHPUnit (' . PHPUnit_Runner_Version::id() . ') is not supported.' . PHP_EOL;
    exit(1);
}

function autoload($className)
{
    $test = dirname(__FILE__) . '/../src/' . str_replace('_', '/', $className) . '.php';
    if (file_exists($test)) {
        require $test;
    }
    $test = dirname(__FILE__) . '/' . str_replace('_', '/', $className) . '.php';
    if (file_exists($test)) {
        require $test;
    }
}

spl_autoload_register('autoload');
