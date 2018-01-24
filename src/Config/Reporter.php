<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 13:30
 */
namespace Tricolor\ZTracker\Config;

class Reporter
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

    /**
     * Log format: 'json' or 'serialize'
     * @var string
     */
    public static $logType = Reporter::json;
    /**
     * @var string
     */
    public static $reporter = Reporter::reporterRabbitMQ;
}