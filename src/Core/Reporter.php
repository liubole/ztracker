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
            switch (Config\Collector::$reporter) {
                case Config\Collector::reporterRabbitMQ:
                    return self::reportSpanByRabbitMQ($spans);
                case Config\Collector::reporterFile:
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
            $message = self::encode($logs, Config\Collector::$logType);
            return Collector\BizLoggerFile::write($message);
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
                case Config\Collector::json:
                    $message = @json_encode($vars);
                    break;
                case Config\Collector::serialize:
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
                case Config\Collector::json:
                    return @json_decode($str, 1);
                case Config\Collector::serialize:
                    return @unserialize($str);
                default:
                    break;
            }
        } catch (\Exception $e) {
        }
        return null;
    }
}