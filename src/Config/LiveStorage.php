<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 20:35
 */
namespace Tricolor\ZTracker\Config;

class LiveStorage
{
    private static $redis_config = array(
        'host' => '',
        'port' => '',
        'timeout' => '',
    );

    public static function getRedis()
    {
        return self::$redis_config;
    }

    public static function setRedis($config)
    {
        foreach (array_intersect_key(self::$redis_config, $config) as $key => $val) {
            self::$redis_config[$key] = $config[$key];
        }
    }
}