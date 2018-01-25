<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 20:35
 */
namespace Tricolor\ZTracker\Config;

use Tricolor\ZTracker\Common;

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
        if (is_array($config)) {
            foreach (array_intersect_key(self::$redis_config, $config) as $key => $val) {
                self::$redis_config[$key] = $config[$key];
            }
        } else {
            Common\Debugger::fatal("redis config is not array!");
        }
    }
}