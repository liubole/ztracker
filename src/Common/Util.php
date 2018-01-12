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
        return microtime();
    }

    public static function duration($start, $end)
    {
        return bcsub(substr($end, 11) . substr($end, 1, 9), substr($start, 11) . substr($start, 1, 9), 8);
    }

    public static function initSpanId()
    {
        return '0';
    }

    public static function uuid()
    {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle), strlen($needle)) === 0;
    }

    public static function childSpanId($fatherSpanId)
    {
        return $fatherSpanId .= '.0';
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
        return empty(array_diff($arr1, $arr2));
    }

    public static function writeHexLong()
    {
        //todo
    }
}