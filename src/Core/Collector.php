<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 13:28
 */
namespace Tricolor\ZTracker\Core;
use \Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Config\TraceEnv;

class Collector
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
            switch (Config\Collector::$reporter)
            {
                case Config\Collector::reporterRabbitMQ:
                    $res = self::reportSpanByRabbitMQ($spans);
                    break;
                case Config\Collector::reporterFile:
                    $res = self::reportSpanToFile($spans);
                    break;
                default:
                    $res = false;
                    break;
            }
            return $res;
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
        if (Collector\Span\RabbitMQ::connect()) {
            $message = self::encode($spans, Config\Collector::$reportType);
            if (Config\Collector::$reportCompress == TraceEnv::COMPRESS_ON) {
                $message = gzdeflate($message);
            }
            Collector\Span\RabbitMQ::pub($message);
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
        if ($spans && Collector\Span\FileLog::ready()) {
            $message = self::encode($spans, Config\Collector::$reportType);
            return Collector\Span\FileLog::write($message);
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
        if ($logs && Collector\Log\FileLog::ready()) {
            $message = self::encode($logs, Config\Collector::$logType);
            return Collector\Log\FileLog::write($message);
        }
        return false;
    }

    /**
     * @param $vars
     * @param $type
     * @return string
     */
    private static function encode(&$vars, $type)
    {
        try {
            switch ($type) {
                case Config\Collector::json:
                    return @json_encode($vars);
                case Config\Collector::serialize:
                    return @serialize($vars);
                default:
                    break;
            }
        } catch (\Exception $e) {
        }
        return "";
    }
}