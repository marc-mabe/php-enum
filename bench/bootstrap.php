<?php

$zendassertions = ini_get('zend.assertions');
if ($zendassertions != -1) {
    echo 'Please disable zend.assertions in php.ini (zend.assertions = -1)' . PHP_EOL
        . "Current ini setting: zend.assertions = {$zendassertions}]" . PHP_EOL;
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';
