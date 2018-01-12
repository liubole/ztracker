<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 22:08
 */
include_once __DIR__ . "/vendor/autoload.php";
include_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/config.php";

use \Tricolor\ZTracker\GlobalTracer;
use \Tricolor\ZTracker\Tracer;
use \Tricolor\ZTracker\Common\Server;
use \Tricolor\ZTracker\Carrier\CarrierType;
use \Tricolor\ZTracker\Common\Util;
use \Tricolor\ZTracker\Core\Enum\SpanKind;

define('CLIENTID', 'Client');
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

function clientInit()
{
    //开关
    $sampleRate = 50;
    $timestamp = Util::current();
    Tracer::localEndpoint(
        $localEndpoint = GlobalTracer::endpointBuilder()
            ->serviceName(Server::getServerApi())
            ->ip(Server::getServerIp())
            ->port(Server::getServerPort())
            ->build()
    );
    Tracer::span(
        $currentSpan = GlobalTracer::spanBuilder()
            ->traceId(Util::uuid())
            ->id(Util::initSpanId())
            ->name(Server::getServerApi())
            ->localEndpoint($localEndpoint)
            ->decision(GlobalTracer::decisionBuilder($sampleRate))
            ->kind(SpanKind::CLIENT)
            ->addAnnotation('cr', $timestamp)
    );
    Tracer::context(
        $context = GlobalTracer::contextBuilder()->build()
    );
}

function client()
{
    //添加记录
    Tracer::span()->addAnnotation('');
    Tracer::span()->putTag('', '');

    //设置透传
    Tracer::context()->set("key0", "value0");

    // rpc
    clientRpc();

    //添加记录
    Tracer::span()->putTag('YourKey', 'your value');

    fastcgi_finish_request();
    //记录
    Tracer::span()->addAnnotation('cs');
    Tracer::flush();
}

function clientMysql()
{
    $span = GlobalTracer::spanBuilder()
        ->childOf(Tracer::span())//no need to call traceId() & id() & parentId()
        ->name('mysql.ylcf_user.query')
        ->shared(false)
        ->localEndpoint(Tracer::localEndpoint())
        ->remoteEndpoint(
            $remoteEndpoint = GlobalTracer::endpointBuilder()
                ->serviceName('mysql')
                ->ip('127.0.0.1')
                ->port('1234')
                ->build());
    $span->addAnnotation('cs', $cs = Util::current());
    //do query
    {}
    //end query
    $span->addAnnotation('cr', $cr = Util::current());
    $span->duration(Util::duration($cs, $cr));
    Tracer::joinSpans($span);
}

function clientRpc()
{
    $headers = array();
    $span = GlobalTracer::spanBuilder()
        ->childOf(Tracer::span())
        ->name('mysql.api')
        ->shared(true)
        ->localEndpoint(Tracer::localEndpoint())
        ->addAnnotation('cs', $cs = Util::current());
    GlobalTracer::newCarrier(CarrierType\HttpHeader)
        ->pipe($headers)
        ->span($span)
        ->context(Tracer::context())
        ->inject();
    // do rpc
    {}
    // end rpc
    $span->addAnnotation('cr', $cr = Util::current());
    $span->duration(Util::duration($cs, $cr));
    Tracer::joinSpans($span);
}

function apiInit()
{
    //segment3:api
    //大括号表示在另一个span里
    $carrier = GlobalTracer::newCarrier(CarrierType\HttpHeader)
        ->pipe($_SERVER)
        ->extract();
    Tracer::context(
        $context = $carrier->getContext()
    );
    $fatherSpan = $carrier->getSpan();
    Tracer::localEndpoint(
        $localEndpoint = GlobalTracer::endpointBuilder()
            ->serviceName(Server::getServerApi())
            ->ip(Server::getServerIp())
            ->port(Server::getServerPort())
            ->build()
    );
    //创建span
    Tracer::span(
        $currentSpan = GlobalTracer::spanBuilder()
            ->childOf($fatherSpan)
            ->name(Server::getServerApi())
            ->kind(SpanKind::SERVER)
            ->localEndpoint(Tracer::localEndpoint())
            ->addAnnotation('sr')
    );

    Tracer::span()->decision->traceOn();//影响
    Tracer::span()->decision->isReportOn();//暂时不用
    Tracer::span()->decision->logOn();//影响span记录/上报

    Tracer::span()->putTag('Output', $output);

    fastcgi_finish_request();

    Tracer::flush();
}

function apiRpc()
{
    $headers = array();
    GlobalTracer::newCarrier(CarrierType\HttpHeader)
        ->pipe($headers)
        ->span(Tracer::span())
        ->context(Tracer::context())
        ->inject();
}

function api()
{
    //获取透传
    $value0 = Tracer::context()->get('key0');


}

