<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 21:22
 */
ini_set('date.timezone', 'Asia/Shanghai');
include_once __DIR__ . "/../config/trace_rabbitmq.php";

use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Core;

// common config
Config\BizLogger::$output = __DIR__ . '/logs/biz-ztrace.log';
Config\Collector::rabbitConfig($trace_rabbitmq);

/*
Config\Storage\Mysql::set(array(
    'host' => 'localhost',
    'port' => '3306',
    'username' => 'root',
    'password' => 'bole123',
    'database' => 'trace',
));
Config\LiveStorage::setRedis(array(
    'host' => '127.0.0.1',
    'port' => '6379',
    'timeout' => 3,
));
Config\Collector::$sampleRate = 50;
Config\BizLogger::$output = "/tmp/biz-ztrace.log";

$tracer = Core\GlobalTracer::tracer();
$tracer->currentSpan()->decision->switchOver(Config\TraceEnv::LOG_ON);
$tracer->currentSpan()->decision->switchOver(Config\TraceEnv::REPORT_ON);
$tracer->currentSpan()->decision->switchOver(Config\TraceEnv::TRACE_ON);

// debug-log
Config\Debug::$ON = true;
Config\Debug::$output = '/tmp/debug-ztrace.log';
*/
