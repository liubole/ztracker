<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 10:13
 */
namespace Tricolor\ZTracker\Common;

class JsonCodec
{
    public static function write($object)
    {
        $array = array();
        try {
            $array = array_merge(
                get_class_vars((new \ReflectionClass($object))->getName()),
                get_object_vars($object)
            );
        } catch (\Exception $e) {
        }
        return json_encode($array);
    }
}