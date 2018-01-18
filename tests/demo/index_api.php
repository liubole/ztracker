<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 22:32
 */
include_once __DIR__ . "/vendor/autoload.php";
include_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/config.php";

use Tricolor\ZTracker\Core\GlobalTracer;
use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Carrier\CarrierType;
use Tricolor\ZTracker\Core\SpanKind;

define('CLIENTID', 'Api');

//Collector::$collector = function ($info) {
//    $call = array('Tricolor\ZTracker\Demo\Logger', 'write');
//    call_user_func_array($call, array('./logs/', $info));
//};

function apiInit()
{
    $tracer = GlobalTracer::tracer();
    $tracer->injector(CarrierType\HttpHeader)->extract();
    $tracer->currentSpan()
        ->name(Util::getServerName())
        ->shared(true)
        ->kind(SpanKind\Server);

    $tracer->currentSpan()->decision->traceOn();//暂时不用
    $tracer->currentSpan()->decision->reportOn();//
    $tracer->currentSpan()->decision->logOn();//影响span记录/上报
    $tracer->currentSpan()->putTag('Output', $output);

    fastcgi_finish_request();

    $tracer->flush();
}

function apiRpc()
{
    $url = "";
    $headers = array();
    $tracer = GlobalTracer::tracer();
    $span = $tracer->newChildSpan()
        ->name(Util::urlPath($url))
        ->shared(1)
        ->kind(SpanKind\Client);
    $tracer->injector(CarrierType\HttpHeader)->inject($headers);
    {
        //real rpc
    }
    $span->end();
}

