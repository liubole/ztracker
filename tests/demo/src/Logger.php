<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 21:52
 */
namespace Tricolor\Tracker\Demo;

class Logger
{
    public static function write($path, $str)
    {
        $dir = rtrim($path, '/') . '/';
        $file = $dir . 's'. date('Ymd') . '.log';
        if (!is_dir($dir)) {
            mkdir($dir, 777, true);
        }
        file_put_contents($file, $str . "\n", FILE_APPEND);
    }
}