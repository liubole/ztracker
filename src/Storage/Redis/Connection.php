<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:44
 */
namespace Tricolor\ZTracker\Storage\Redis;

use Tricolor\ZTracker\Common;

class Connection
{
    private static $conns = array();

    /**
     * @param $config
     * @return \Redis|null
     * @throws \Exception
     */
    public static function getConnection($config)
    {
        try {
            $host = Common\Util::checkNotNull($config['host'], 'redis config.host is null!');
            $port = Common\Util::checkNotNull($config['port'], 'redis config.port is null!');
            $timeout = (int)$config['timeout'];
            $key = '_' . $host . '_' . $port;
            if (!isset(self::$conns[$key]) || !self::$conns[$key]) {
                $redis = new \Redis();
                $redis->pconnect($host,$port, $timeout);
                self::$conns[$key] = $redis;
            }
            return self::$conns[$key];
        } catch (\Exception $e) {
            Common\Debugger::fatal($e->getMessage());
            throw $e;
        }
    }
}