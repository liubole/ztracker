<?php
/**
 * User: Tricolor
 * Date: 2018/1/19
 * Time: 14:39
 */
// required
if (PHP_INT_MAX === 2147483647) {
    printf('64-bit Architecture is required.' . PHP_EOL);
}
if (version_compare(PHP_VERSION, $min = '5.6.0') < 0) {
    printf('PHP version cannot be lower %s.' . PHP_EOL, $min);
}
if (!extension_loaded('pdo_mysql')) {
    printf('It\'s required to install extension %s.' . PHP_EOL, 'pdo_mysql');
}
if (!extension_loaded('zlib')) {
    printf('It\'s required to install extension %s.' . PHP_EOL, 'zlib');
}

// recommend
if (!extension_loaded('pcntl')) {
    printf('It\'s recommend to install extension %s.' . PHP_EOL, 'pcntl');
}
