<?php

$zendassertions = ini_get('zend.assertions');
if (\PHP_VERSION_ID >= 70000 && $zendassertions != -1) {
    echo 'Please disable zend.assertions in php.ini (zend.assertions = -1)' . PHP_EOL
        . "Current ini setting: zend.assertions = {$zendassertions}]" . PHP_EOL;
    exit(1);
}
assert_options(ASSERT_ACTIVE, 0);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 0);

require_once __DIR__ . '/../vendor/autoload.php';
