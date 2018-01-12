<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 13:35
 */
namespace Tricolor\ZTracker\Config\Collector\Span;

class RabbitMQ
{
    public static $exchange = 'ztrace';
    public static $queue = 'ztrace';
    public static $key = 'ztrace.log';

    private static $config = array(
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
        return self::$config;
    }

    public static function set($config)
    {
        if (is_array($config)) {
            self::$config = array_merge(self::$config, $config);
        }
    }
}