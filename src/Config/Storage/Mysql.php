<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:29
 */
namespace Tricolor\ZTracker\Config\Storage;

use Tricolor\ZTracker\Common;

class Mysql
{
    public static $host = "";
    public static $port = "";
    public static $username = "";
    public static $password = "";
    public static $database = "";

    public static function set($db)
    {
        if (is_array($db)) {
            foreach (array_intersect_key(get_class_vars(__CLASS__), $db) as $key => $v) {
                self::$$key = $db[$key];
            }
        } else {
            Common\Debugger::fatal("mysql config is not array!");
        }
    }

    public static function get()
    {
        return get_class_vars(__CLASS__);
    }
}