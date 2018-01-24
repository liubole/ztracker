<?php
/**
 * User: Tricolor
 * Date: 2018/1/24
 * Time: 18:27
 */
namespace Tricolor\ZTracker\Common;

use Tricolor\ZTracker\Common;

class Logger
{
    const WARNING = 1;
    const ERROR = 2;

    public static function warning($message)
    {
        self::write(self::WARNING, $message);
    }

    public static function error($message)
    {
        self::write(self::ERROR, $message);
    }

    private static function write($level, $message)
    {
        if (!$message || !($file = self::ready())) {
            return false;
        }
        $at = Common\Util::currentInHuman();
        if (($idx = strrpos($at, '.')) !== false) {
            $gap = strlen($at) - strrpos($at, '.') - 1;
            if ($gap < 8) $at .= str_repeat('0', 8 - $gap);
        }
        $lev = self::resolve($level);
        return file_put_contents($file, "[$at][$lev]$message" . PHP_EOL, FILE_APPEND);
    }

    private static function resolve($level)
    {
        switch ($level) {
            case self::WARNING:
                return "WARNING";
            case self::ERROR:
                return "ERROR";
            default:
                return "UNKNOWN";
        }
    }

    private static function ready()
    {
        $root = self::getRoot();
        if (!$root) {
            return false;
        }
        if (!is_dir($root) AND !mkdir($root, 766, true)) {
            return false;
        }
        $file = self::getFileName();
        if (file_exists($file) OR touch($file)) {
            return $file;
        }
        return false;
    }

    private static function getFileName()
    {
        return self::getRoot() . '/ztrace-debug.log';
    }

    private static function getRoot()
    {
        return '/tmp';
    }
}