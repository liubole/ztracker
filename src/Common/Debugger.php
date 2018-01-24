<?php
/**
 * User: Tricolor
 * Date: 2018/1/24
 * Time: 18:27
 */
namespace Tricolor\ZTracker\Common;

use Tricolor\ZTracker\Config;

class Debugger
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
        if (!Config\Debug::$ON) {
            return false;
        }
        $at = date('Y-m-d H:i:s');
        $lev = self::resolve($level);
        return file_put_contents($file, "[$at] [$lev] $message" . PHP_EOL, FILE_APPEND);
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
        $root = self::getRoot();
        if (!Config\Debug::$output) {
            $base = pathinfo(Config\Debug::$output, PATHINFO_BASENAME);
            $ext = pathinfo(Config\Debug::$output, PATHINFO_EXTENSION);
            $file = $ext
                ? substr($base, 0, strrpos($base, $ext)) . $ext
                : $base;
            return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
        }
        return $root . '/debug-ztrace.log';
    }

    private static function getRoot()
    {
        if (!Config\Debug::$output) {
            return "/tmp";
        }
        return pathinfo(Config\Debug::$output, PATHINFO_DIRNAME);
    }
}