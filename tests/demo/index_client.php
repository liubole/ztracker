<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 22:08
 */
include_once __DIR__ . "/vendor/autoload.php";
include_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/config.php";

use \Tricolor\ZTracker\Core\GlobalTracer;
use \Tricolor\ZTracker\Common\Util;
use \Tricolor\ZTracker\Carrier\CarrierType;
use \Tricolor\ZTracker\Core\SpanKind;

define('CLIENTID', 'Client');
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

function clientContent()
{
    // index
    $tracer = GlobalTracer::tracer();
    {
        $sampleRate = 50;
        $tracer->newSpan()
            ->kind(SpanKind\Server)
            ->shared(0)
            ->decision(GlobalTracer::decisionBuilder($sampleRate));
    }
    $tracer->currentSpan()->putTag('', '');
    $tracer->setContext("key0", "value0");

    {
        // rpc
        clientDoRpc();
    }

    {
        // mysql
        clientMysql();
    }

    //添加记录
    $tracer->currentSpan()->putTag('YourKey', 'your value')->end();
    $tracer->currentSpan()->end();

    fastcgi_finish_request();

    //记录
    $tracer->flush();
}

function clientDoRpc()
{
    $url = "";
    $headers = array();
    $tracer = GlobalTracer::tracer();
    $span = $tracer->newChildSpan()
        ->name(Util::urlServerApi($url))
        ->shared(1)
        ->kind(SpanKind\Client)
        /**->remoteEndpoint(
            GlobalTracer::endpointBuilder()
                ->serviceName('mysql')
                ->ip('127.0.0.1')
                ->port('1234')
        )*/;
    $tracer->injector(CarrierType\HttpHeader)->inject($headers);
    {
        // real rpc
    }
    $span->end();
}

function clientMysql()
{
    $tracer = GlobalTracer::tracer();
    $span = $tracer->newChildSpan()
        ->name('mysql.user.query')
        ->shared(false)
        ->kind(SpanKind\Client)
        ->remoteEndpoint(
            GlobalTracer::endpointBuilder()
                ->serviceName('mysql')
                ->ip('127.0.0.1')
                ->port('1234')
        );
    {
        // real query
    }
    $span->end();
}



