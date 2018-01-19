<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 13:35
 */
namespace Tricolor\ZTracker\Config;

class TraceCollector
{
    // file collector
    public static $root = '/tmp';
    public static $log_name = 'ztrace.log';

    // rabbitmq collector
    public static $rabbit_exchange = 'ztrace';
    public static $rabbit_queue = 'ztrace';
    public static $rabbit_key = 'ztrace.log';

    private static $rabbit_config = array(
        'host' => '127.0.0.1',
        'port' => 5672,
        'user' => 'ztrace',
        'password' => 'ztrace',
        'vhost' => 'ztrace',
        'insist' => false,
        'login_method' => 'AMQPLAIN',
        'login_response' => null,
        'locale' => 'en_US',
        'connection_timeout' => 3,
        'read_write_timeout' => 3,
        'context' => null,
        'keepalive' => false,
        'heartbeat' => 0,
    );

    public static function get()
    {
        return self::$rabbit_config;
    }

    public static function set($config)
    {
        if (is_array($config)) {
            self::$rabbit_config = array_merge(self::$rabbit_config, $config);
        }
    }
}