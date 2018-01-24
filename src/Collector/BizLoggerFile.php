<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 14:55
 */
namespace Tricolor\ZTracker\Collector;

use Tricolor\ZTracker\Config;

class BizLoggerFile
{
    public static function write($message, $day = null)
    {
        if (!$message || !($file = self::ready($day))) {
            return false;
        }
        return file_put_contents($file, $message . PHP_EOL, FILE_APPEND);
    }

    public static function ready($day = null)
    {
        $root = self::getRoot();
        if (!$root) {
            return false;
        }
        if (!is_dir($root) AND !mkdir($root, 766, true)) {
            return false;
        }
        $file = self::getFileName($day);
        if (file_exists($file) OR touch($file)) {
            return $file;
        }
        return false;
    }

    private static function getFileName($day = null)
    {
        $day = $day ? date("Ymd", strtotime($day)) : date("Ymd");
        $root = self::getRoot();
        $logname = Config\BizLogger::$log_name
            ? Config\BizLogger::$log_name
            : "biz-ztrace.log";
        $idx = strrpos($logname, '.');
        $file = $idx !== false
            ? substr($logname, 0, $idx) . '.' . $day . substr($logname, $idx)
            : $logname . '.' . $day;
        return rtrim($root, '/') . '/' . $file;
    }

    private static function getRoot()
    {
        return Config\BizLogger::$root;
    }

    public function logger($fileName)
    {
        $fileHandle = fopen($fileName, 'a');
        while (true) {
            fwrite($fileHandle, yield . PHP_EOL);
        }
    }

}