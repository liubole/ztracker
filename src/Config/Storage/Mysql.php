<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:29
 */
namespace Tricolor\ZTracker\Config\Storage;

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
            foreach (array_intersect_key(get_class_vars(__NAMESPACE__ . DIRECTORY_SEPARATOR . 'Mysql'), $db) as $key => $v) {
                self::$$key = $db[$key];
            }
        }
    }

    public static function get()
    {
        return get_class_vars(__NAMESPACE__ . DIRECTORY_SEPARATOR . 'Mysql');
    }
}