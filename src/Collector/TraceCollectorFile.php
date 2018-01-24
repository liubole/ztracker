<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 14:55
 */
namespace Tricolor\ZTracker\Collector;

use Tricolor\ZTracker\Config;

class TraceCollectorFile
{
    public static function write($message)
    {
        if (!$message || !($file = self::ready())) {
            return false;
        }
        return file_put_contents($file, $message . PHP_EOL, FILE_APPEND);
    }

    public static function ready()
    {
        $root = Config\Collector::$root;
        if (!$root) {
            return false;
        }
        if (!is_dir($root) AND !mkdir($root, 766, true)) {
            return false;
        }
        $logname = Config\Collector::$log_name;
        $file = rtrim($root, '/') . '/' . ($logname ? $logname : "trace.log");
        if (file_exists($file) OR touch($file)) {
            return $file;
        }
        return false;
    }

    public function logger($fileName)
    {
        $fileHandle = fopen($fileName, 'a');
        while (true) {
            fwrite($fileHandle, yield . PHP_EOL);
        }
    }
}