<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 13:28
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Collector;

class Reporter
{
    /**
     * Collect spans && logs
     * @param $spans array
     * @param $logs array
     */
    public static function collect(&$spans, &$logs)
    {
        self::reportSpans($spans);
        self::logLogs($logs);
    }

    /**
     * Collect spans
     * @param $spans
     * @return bool
     */
    private static function reportSpans(&$spans)
    {
        try {
            switch (Config\Reporter::$reporter) {
                case Config\Reporter::reporterRabbitMQ:
                    return self::reportSpanByRabbitMQ($spans);
                case Config\Reporter::reporterFile:
                    return self::reportSpanToFile($spans);
                default:
                    return false;
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * @param $spans
     * @return bool
     */
    private static function reportSpanByRabbitMQ(&$spans)
    {
        if ($spans && Collector\TraceCollectorRabbitMQ::connect()) {
            $message = Common\Compress::spansCompress($spans);
            Collector\TraceCollectorRabbitMQ::pub($message);
            return true;
        }
        return false;
    }

    /**
     * @param $spans
     * @return bool|int
     */
    private static function reportSpanToFile(&$spans)
    {
        if ($spans && Collector\TraceCollectorFile::ready()) {
            $message = Common\Compress::spansCompress($spans, false);
            return Collector\TraceCollectorFile::write($message);
        }
        return false;
    }

    /**
     * Collect logs
     * @param $logs
     * @return bool
     */
    private static function logLogs(&$logs)
    {
        if ($logs && Collector\BizLoggerFile::ready()) {
            $day = self::pickDay($logs);
            $message = self::encode($logs, Config\Reporter::$logType);
            return Collector\BizLoggerFile::write($message, $day);
        }
        return false;
    }

    /**
     * @param $vars
     * @param $type
     * @param $compress
     * @return string
     */
    public static function encode(&$vars, $type, $compress = false)
    {
        try {
            switch ($type) {
                case Config\Reporter::json:
                    $message = @json_encode($vars);
                    break;
                case Config\Reporter::serialize:
                    $message = @serialize($vars);
                    break;
                default:
                    return "";
            }
            return $compress ? gzdeflate($message) : $message;
        } catch (\Exception $e) {
        }
        return "";
    }

    /**
     * @param $str
     * @param $type
     * @param bool $compressed
     * @return mixed|null
     */
    public static function decode($str, $type, $compressed = true)
    {
        try {
            $str = $compressed ? gzinflate($str) : $str;
            switch ($type) {
                case Config\Reporter::json:
                    return @json_decode($str, 1);
                case Config\Reporter::serialize:
                    return @unserialize($str);
                default:
                    break;
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    private static function pickDay($logs)
    {
        if (isset($logs['timestamp'])) {
            $tmp = explode('.', $logs['timestamp']);
            return date("Ymd", strtotime($tmp[0]));
        }
        return date("Ymd");
    }
}