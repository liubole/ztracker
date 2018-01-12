<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 22:32
 */
include_once __DIR__ . "/vendor/autoload.php";
include_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/config.php";

use \Tricolor\Tracker\Tracer;
use \Tricolor\Tracker\Config\Collector;
use \Tricolor\Tracker\Carrier\HttpHeaders;

define('CLIENTID', 'Api');

//Collector::$collector = function ($info) {
//    $call = array('\Tricolor\Tracker\Demo\Logger', 'write');
//    call_user_func_array($call, array('./logs/', $info));
//};
Tracer::extract(new HttpHeaders());
Tracer::instance()
    ->log('post', $_POST)
    ->log('get', $_GET)
    ->tag('ApiRecv')
    ->run();

$api = new \Tricolor\Tracker\Demo\Api();
$api->doTouch();

