<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 17:43
 */
namespace Tricolor\ZTracker\Server\Shell;

use Tricolor\ZTracker\Collector;
use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Server;

/**
 * Usage:
 * 1.php consumer.php
 * 2.consumer.php:
 *  $collector = new Server\Shell\RabbitMQConsumer();
 *  $collector->run();
 * Class RabbitMQConsumer
 * @package Tricolor\ZTracker\Server\Shell
 */
class RabbitMQConsumer
{
    private $handler;

    public function run()
    {
        $span_handler = new Server\Jobs\Trace();
        $handler = isset($this->handler)
            ? $this->handler
            : function ($msg) use (&$span_handler) {
            $body = $msg->body;
            $spans = Common\Compress::spansUnCompress($body);
            $span_handler->accept($spans);
        };
        Collector\TraceCollectorRabbitMQ::sub($handler);
    }

    /**
     * @param $handler
     * @return RabbitMQConsumer
     */
    public function handler($handler)
    {
        is_callable($handler) AND ($this->handler = $handler);
        return $this;
    }
}