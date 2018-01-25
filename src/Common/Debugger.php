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
    const FATAL = 1;
    const ERROR = 2;
    const WARNING = 3;
    const NOTICE = 4;
    const INFO = 5;

    public static function fatal($message)
    {
        self::write(self::FATAL, $message);
    }

    public static function error($message)
    {
        self::write(self::ERROR, $message);
    }

    public static function warning($message)
    {
        self::write(self::WARNING, $message);
    }

    public static function notice($message)
    {
        self::write(self::NOTICE, $message);
    }

    public static function info($message)
    {
        self::write(self::INFO, $message);
    }

    private static function write($level, $message)
    {
        if (!$message || !($file = self::ready())) {
            return false;
        }
        if (!Config\Debug::$ON) {
            return false;
        }
        if (!is_string($message)) {
            $message = var_export($message, 1);
        }
        $at = date('Y-m-d H:i:s');
        $lev = self::resolve($level);
        return file_put_contents($file, "[$at] [$lev] $message" . PHP_EOL, FILE_APPEND);
    }

    private static function resolve($level)
    {
        switch ($level) {
            case self::FATAL:
                return "FATAL";
            case self::ERROR:
                return "ERROR";
            case self::WARNING:
                return "WARNING";
            case self::NOTICE:
                return "NOTICE";
            case self::INFO:
                return "INFO";
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