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
        if (!Config\BizLogger::$output) {
            $base = pathinfo(Config\BizLogger::$output, PATHINFO_BASENAME);
            $ext = pathinfo(Config\BizLogger::$output, PATHINFO_EXTENSION);
            $file = $ext
                ? substr($base, 0, strrpos($base, $ext)) . $day . '.' . $ext
                : $base . '.' . $day;
            return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
        }
        return $root . '/biz-ztrace.' . $day . '.log';
    }

    private static function getRoot()
    {
        if (!Config\BizLogger::$output) {
            return "/tmp";
        }
        return pathinfo(Config\BizLogger::$output, PATHINFO_DIRNAME);
    }

    public function logger($fileName)
    {
        $fileHandle = fopen($fileName, 'a');
        while (true) {
            fwrite($fileHandle, yield . PHP_EOL);
        }
    }

}