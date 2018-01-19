<?php
/**
 * User: Tricolor
 * Date: 2018/1/19
 * Time: 14:39
 */
// required
if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    printf('PHP version cannot be lower 5.4.0.' . PHP_EOL);
}
if (!extension_loaded('pdo_mysql')) {
    printf('It\'s required to install extension %s.' . PHP_EOL, 'pdo_mysql');
}

// recommend
if (!extension_loaded('pcntl')) {
    printf('It\'s recommend to install extension %s.' . PHP_EOL, 'pcntl');
}
