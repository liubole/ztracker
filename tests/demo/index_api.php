<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 22:32
 */
include_once __DIR__ . "/vendor/autoload.php";
include_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/config.php";

use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Carrier\CarrierType;
use Tricolor\ZTracker\Core;

define('CLIENTID', 'Api');

//Collector::$collector = function ($info) {
//    $call = array('Tricolor\ZTracker\Demo\Logger', 'write');
//    call_user_func_array($call, array('./logs/', $info));
//};

function apiInit()
{
    $tracer = Core\GlobalTracer::tracer();
    $tracer->extract(CarrierType\HttpHeader);
    // or
//    {
//        $tracer->injector(CarrierType\HttpHeader)
//            ->pipe($_SERVER)
//            ->extract($span, $context);
//        if (isset($span) && ($span instanceof Core\Span)) {
//            $span->localEndpoint($this->localEndpoint());
//        }
//        $tracer->currentContext($context);
//        $tracer->currentSpan($span);
//        $tracer->joinSpan($span);
//    }
    if (!is_null($tracer->currentSpan())) {
        $tracer->currentSpan()
            ->name(Util::getServerName())
            ->shared(true)
            ->kind(Core\SpanKind\Server);

        $tracer->currentSpan()->decision->traceOn();//暂时不用
        $tracer->currentSpan()->decision->reportOn();//
        $tracer->currentSpan()->decision->logOn();//影响span记录/上报
        $tracer->currentSpan()->putTag('Output', $output);
    }

    fastcgi_finish_request();

    $tracer->flush();
}

function apiRpc()
{
    $url = "";
    $headers = array();
    $tracer = Core\GlobalTracer::tracer();
    $span = $tracer->newChildSpan()
        ->name(Util::urlPath($url))
        ->shared(1)
        ->kind(Core\SpanKind\Client);
    $tracer->inject(CarrierType\HttpHeader, $headers);
    {
        //real rpc
    }
    $span->end();
}

