<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 21:22
 */
ini_set('date.timezone', 'Asia/Shanghai');
include_once __DIR__ . "/../config/trace_rabbitmq.php";
use Tricolor\ZTracker\Config;

Config\BizLogger::$root = __DIR__ . '/logs';
Config\TraceCollector::rabbitConfig($trace_rabbitmq);