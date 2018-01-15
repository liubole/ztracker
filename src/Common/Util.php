<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 16:38
 */
namespace Tricolor\ZTracker\Common;

use Tricolor\ZTracker\Exception\IllegalArgumentException;
use Tricolor\ZTracker\Exception\NullPointerException;

class Util
{
    const UTF_8 = "UTF-8";

    public static function current()
    {
        $str = microtime();
        return substr($str, 11) . substr($str, 1, 9);
    }

    public static function duration($start, $end)
    {
        return bcsub($end, $start, 8);
    }

    public static function uuid()
    {
        return self::random(32);
    }

    public static function spanId()
    {
        return self::random(16);
    }

    public static function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle), strlen($needle)) === 0;
    }

    public static function checkNotNull($reference, $errorMessage)
    {
        if (is_null($reference)) {
            throw new NullPointerException($errorMessage);
        }
        return $reference;
    }

    public static function checkArgument($expression, $errorMessageTemplate, $_errorMessageArgs = null)
    {
        if (!$expression) {
            $message = (func_num_args() >= 3)
                ? call_user_func_array('sprintf', array_merge((array)$errorMessageTemplate, array_slice(func_get_args(), 2)))
                : $errorMessageTemplate;
            throw new IllegalArgumentException($message);
        }
    }

    public static function equal($a, $b)
    {
        return $a == $b;
    }

    public static function sortedList($in)
    {
        if (empty($in)) return array();
        if (count($in) == 1) return $in;
        asort($in);
        return $in;
    }

    public static function arrayEqual($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) return false;
        $diff = array_diff($arr1, $arr2);
        return empty($diff);
    }

    private static function random($length)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
            return substr(bin2hex($bytes), 0, $length);
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
            return substr(bin2hex($bytes), 0, $length);
        } else {
            $pool = 'abcdefghijklmnopqrstuvwxyz0123456789';
            return substr(str_shuffle(str_repeat($pool, ceil($length / strlen($pool)))), 0, $length);
        }
    }

    public static function getServerName()
    {
        if (isset($_SERVER) && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    public static function urlPath($url)
    {
        $vars = parse_url($url);
        return isset($vars['path']) ? $vars['path'] : '/';
    }

    public static function urlHost($url)
    {
        $vars = parse_url($url);
        return isset($vars['host']) ? $vars['host'] : '';
    }

    public static function urlQuery($url)
    {
        $vars = parse_url($url);
        return isset($vars['query']) ? $vars['query'] : '';
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