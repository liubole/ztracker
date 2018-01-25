<?php
/**
 * User: Tricolor
 * Date: 2018/1/24
 * Time: 19:28
 */
namespace Tricolor\ZTracker\Config;

class Debug
{
    const FATAL = 1;
    const ERROR = 2;
    const WARNING = 3;
    const NOTICE = 4;
    const INFO = 5;
    
    public static $ON = false;

    public static $output = "/tmp/debug-ztrace.log";
}