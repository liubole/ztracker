<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 10:36
 */
namespace Tricolor\ZTracker\Common;

class Server
{
    public static function getServerName()
    {
        if (isset($_SERVER) && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    public static function getServerApiByUrl($url)
    {
        $url = strpos($url, 'http') === 0 ? substr($url, strpos($url, '/') + 2) : $url;
        $url = strpos($url, '#') !== false ? substr($url, 0, strpos($url, '#')) : $url;
        if (($idx = strpos($url, '?')) !== false) {
            return substr($url, 0, $idx);
        }
        return $url;
    }

    public static function getServerApi()
    {
        if (!isset($_SERVER) || !isset($_SERVER['REQUEST_URI'])) return '';
        if (($idx = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
            return substr($_SERVER['REQUEST_URI'], 0, $idx);
        }
        return $_SERVER['REQUEST_URI'];
    }

    public static function getServerIp()
    {
        if (isset($_SERVER) && isset($_SERVER['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }
        if (isset($_SERVER) && isset($_SERVER['HOSTNAME']) && function_exists('gethostbyname')) {
            return gethostbyname($_SERVER['HOSTNAME']);
        }
        $result = shell_exec("/sbin/ifconfig");
        if (preg_match_all("/addr:(\d+\.\d+\.\d+\.\d+)/", $result, $match) !== 0) {
            foreach ($match[0] as $k => $v) {
                if ($match[1][$k] != "127.0.0.1")
                    return $match[1][$k];
            }
        }
        return '';
    }

    public static function getServerPort()
    {
        if (isset($_SERVER) && isset($_SERVER["SERVER_PORT"])) {
            return $_SERVER["SERVER_PORT"];
        }
        return 0;
    }
}