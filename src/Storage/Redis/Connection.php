<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:44
 */
namespace Tricolor\ZTracker\Storage\Redis;

use Tricolor\ZTracker\Common\Util;

class Connection
{
    private static $conns = array();

    /**
     * @param $config
     * @return \Redis
     * @throws \Exception
     */
    public static function getConnection($config)
    {
        try {
            $host = Util::checkNotNull($config['host'], 'redis config.host is null!');
            $port = Util::checkNotNull($config['port'], 'redis config.port is null!');
            $timeout = (int)$config['timeout'];
            $uniq = '_' . $host . '_' . $port;
            if (!self::$conns[$uniq]) {
                $redis = new \Redis();
                $redis->pconnect($host,$port, $timeout);
                self::$conns[$uniq] = $redis;
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return self::$conns[$uniq];
    }
}