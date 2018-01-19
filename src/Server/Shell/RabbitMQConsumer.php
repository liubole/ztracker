<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 17:43
 */
namespace Tricolor\ZTracker\Server\Shell;
include_once __DIR__ . "/../../../vendor/autoload.php";

use Tricolor\ZTracker\Collector;
use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Server;

$span_handler = new Server\Jobs\Trace();
Collector\TraceCollectorRabbitMQ::sub(function ($msg) use (&$span_handler) {
    $body = $msg->body;
    $spans = Common\Compress::spansUnCompress($body);
    $span_handler->accept($spans);
});