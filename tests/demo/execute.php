<?php
/**
 * User: Tricolor
 * Date: 2018/1/5
 * Time: 10:17
 */
include_once __DIR__ . "/vendor/autoload.php";
include_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/functions.php";

for($i = 1; $i <= 100; $i++) {
    $url = 'http://client.tricolor.com/index_client.php';
    echo "call ".str_pad($i, 3) ." >>";
    $res = curl($url, randParams(), randUa(), array(), $status);
    echo "\tstatus: $status, end!\n";
}