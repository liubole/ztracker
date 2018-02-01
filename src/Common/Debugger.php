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
    public static function fatal($message)
    {
        self::write(Config\Debug::FATAL, $message);
    }

    public static function error($message)
    {
        self::write(Config\Debug::ERROR, $message);
    }

    public static function warning($message)
    {
        self::write(Config\Debug::WARNING, $message);
    }

    public static function notice($message)
    {
        self::write(Config\Debug::NOTICE, $message);
    }

    public static function info($message)
    {
        self::write(Config\Debug::INFO, $message);
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
            case Config\Debug::FATAL:
                return "FATAL";
            case Config\Debug::ERROR:
                return "ERROR";
            case Config\Debug::WARNING:
                return "WARNING";
            case Config\Debug::NOTICE:
                return "NOTICE";
            case Config\Debug::INFO:
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
        if (!is_dir($root)) {
            $old = umask(0);
            $mk = @mkdir($root, 0777, true);
            umask($old);
            if (!$mk) {
                return false;
            }
        }
        $file = self::getFileName();
        if (!file_exists($file)) {
            $old = umask(0);
            $th = @touch($file);
            umask($old);
            return $th ? $file : false;
        }
        return $file;
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