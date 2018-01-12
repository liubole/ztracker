<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 13:30
 */
namespace Tricolor\ZTracker\Config;

class Collector
{
    /**
     * log type: json or serialization
     */
    const json = 'json';
    const serialize = 'serialize';

    /**
     * Reporter
     * report important data to server
     */
    const reporterRabbitMQ = 'rabbitmq';
    const reporterFile = 'file';

    /**
     * Log format: 'json' or 'serialize'
     * @var string
     */
    public static $logType = Collector::json;
    /**
     * @var string
     */
    public static $reporter = Collector::reporterRabbitMQ;
    /**
     * Compress or not
     * @var int
     */
    public static $reportCompress = TraceEnv::COMPRESS_ON;
    /**
     * Report format: 'json' or 'serialize'
     * @var string
     */
    public static $reportType = Collector::json;
}