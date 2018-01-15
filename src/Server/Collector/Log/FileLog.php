<?php
/**
 * User: Tricolor
 * Date: 2018/1/12
 * Time: 14:55
 */
namespace Tricolor\ZTracker\Server\Collector\Log;

use Tricolor\ZTracker\Config;

class FileLog
{
    public static function write($message)
    {
        if (!$message || !($file = self::ready())) {
            return false;
        }
        return file_put_contents($file, $message . "\n", FILE_APPEND);
    }

    public static function ready()
    {
        $root = Config\Collector\Log\FileLog::$root;
        if (!$root) {
            return false;
        }
        if (!is_dir($root) AND !mkdir($root, 766, true)) {
            return false;
        }
        $logname = Config\Collector\Log\FileLog::$log_name;
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
            fwrite($fileHandle, yield . "\n");
        }
    }
}