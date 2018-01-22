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

    public static function sampleOrNot($rate)
    {
        return mt_rand(1, 100) <= (int)$rate;
    }

    /**
     * @return string
     */
    public static function currentTimeMillis()
    {
        $str = microtime(true);
        return bcmul($str, 1000, 0);
    }

    /**
     * @return string
     */
    public static function current()
    {
        $str = microtime();
        return bcmul(substr($str, 11) . substr($str, 1, 9), 1000000, 0);
    }

    /**
     * @param null $current
     * @return string
     */
    public static function currentInHuman($current = null)
    {
        $current = isset($current) ? $current : self::current();
        return date('Y-m-d H:i:s', substr($current, 0, 10)) . '.' . substr($current, 10);
    }

    /**
     * @param $start
     * @param $end
     * @return string
     */
    public static function duration($start, $end)
    {
        return bcsub($end, $start, 0);
    }

    /**
     * @param $start
     * @param $duration
     * @return string
     */
    public static function endTs($start, $duration)
    {
        return bcadd($start, $duration, 0);
    }

    /**
     * @return string
     */
    public static function traceId()
    {
        return base_convert(substr(self::random(16), 0, 15), 16, 10);
//        return self::random(32);
    }

    /**
     * @return string
     */
    public static function spanId()
    {
        $time = microtime();
        return substr($time, 20, 1) . substr($time, 2, 6) . str_pad(getmypid(), 5, '0') . mt_rand(100, 999);
//        return self::random(16);
    }

    /**
     * @param $end_ts int|float time in micro
     * @param $lookback
     * @return array
     */
    public static function getDays($end_ts, $lookback)
    {
        date("Y-d-m", $end_ts);
        $dt_start = bcdiv(bcsub($end_ts, $lookback, 0), 1000, 0);
        $dt_end = bcdiv($end_ts, 1000, 0);
        $days = array();
        do {
            $days[] = date('Y-m-d', $dt_start);
        } while (($dt_start += 86400) <= $dt_end);
        return $days;
    }

    /**
     * @param $day
     * @param $timezone
     * @return string seconds
     */
    public static function midnightUTC($day, $timezone)
    {
        return date_create($day, timezone_open($timezone))->getTimestamp();
    }

    /**
     * @return int
     */
    public static function daysToMicros()
    {
        return 86400000000;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle), strlen($needle)) === 0;
    }

    /**
     * @param $reference
     * @param $errorMessage
     * @return mixed
     * @throws NullPointerException
     */
    public static function checkNotNull($reference, $errorMessage)
    {
        if (is_null($reference)) {
            throw new NullPointerException($errorMessage);
        }
        return $reference;
    }

    /**
     * @param $expression
     * @param $errorMessageTemplate
     * @param null $_errorMessageArgs
     * @throws IllegalArgumentException
     */
    public static function checkArgument($expression, $errorMessageTemplate, $_errorMessageArgs = null)
    {
        if (!$expression) {
            $message = self::strFormatArgs($errorMessageTemplate, array_slice(func_get_args(), 2));
            var_dump('checkArgument: ' . $message);
        }
    }

    /**
     * @param $str
     * @param null $_
     * @return mixed
     */
    public static function strFormat($str, $_ = null)
    {
        return (func_num_args() >= 2)
            ? call_user_func_array('sprintf', array_merge((array)$str, array_slice(func_get_args(), 1)))
            : $str;
    }

    /**
     * @param $str
     * @param $args
     * @return mixed
     */
    public static function strFormatArgs($str, $args)
    {
        return $args
            ? call_user_func_array('sprintf', array_merge((array)$str, $args))
            : $str;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function equal($a, $b)
    {
        return $a == $b;
    }

    /**
     * @param $in
     * @return array
     */
    public static function sortedList($in)
    {
        if (empty($in)) return array();
        if (count($in) == 1) return $in;
        asort($in);
        return $in;
    }

    /**
     * @param $arr1
     * @param $arr2
     * @return bool
     */
    public static function arrayEqual($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) return false;
        $diff = array_diff($arr1, $arr2);
        return empty($diff);
    }

    /**
     * @param $length
     * @return string
     */
    public static function random($length)
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

    /**
     * @return string
     */
    public static function getServerName()
    {
        if (isset($_SERVER) && isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return '';
    }

    /**
     * @param $url
     * @return string
     */
    public static function urlPath($url)
    {
        $vars = parse_url($url);
        return isset($vars['path']) ? $vars['path'] : '/';
    }

    /**
     * @param $url
     * @return string
     */
    public static function urlHost($url)
    {
        $vars = parse_url($url);
        return isset($vars['host']) ? $vars['host'] : '';
    }

    /**
     * @param $url
     * @return string
     */
    public static function urlQuery($url)
    {
        $vars = parse_url($url);
        return isset($vars['query']) ? $vars['query'] : '';
    }

    /**
     * @return string
     */
    public static function getServerApi()
    {
        if (!isset($_SERVER) || !isset($_SERVER['REQUEST_URI'])) return '';
        if (($idx = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
            return substr($_SERVER['REQUEST_URI'], 0, $idx);
        }
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
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

    /**
     * @return int
     */
    public static function getServerPort()
    {
        if (isset($_SERVER) && isset($_SERVER["SERVER_PORT"])) {
            return $_SERVER["SERVER_PORT"];
        }
        return 0;
    }
}