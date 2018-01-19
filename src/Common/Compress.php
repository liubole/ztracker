<?php
/**
 * User: Tricolor
 * Date: 2018/1/18
 * Time: 19:03
 */
namespace Tricolor\ZTracker\Common;

use Tricolor\ZTracker\Core;

class Compress
{
    const SPAN_MAP = array(
        'traceId' => 'a',
        'name' => 'b',
        'id' => 'c',
        'parentId' => 'd',
        'decision' => 'e',
        'localEndpoint' => 'f',
        'kind' => 'g',
        'shared' => 'h',
        'remoteEndpoint' => 'i',
        'annotations' => 'j',
        'binaryAnnotations' => 'k',
        'timestamp' => 'l',
        'duration' => 'm',
        'tags' => 'n',
        'debug' => 'o',
    );

    const ENDPOINT_MAP = array(
        'serviceName' => 'a',
        'ipv4' => 'b',
        'ipv6' => 'c',
        'port' => 'd',
    );

    const ANNOTATION_MAP = array(
        'timestamp' => 'a',
        'value' => 'b',
        'endpoint' => 'c',
    );

    const BINARYANNOTATION_MAP = array(
        'key' => 'a',
        'value' => 'b',
        'type' => 'c',
        'endpoint' => 'd',
    );

    const MAP_SPAN = array(
        'a' => 'traceId',
        'b' => 'name',
        'c' => 'id',
        'd' => 'parentId',
        'e' => 'decision',
        'f' => 'localEndpoint',
        'g' => 'kind',
        'h' => 'shared',
        'i' => 'remoteEndpoint',
        'j' => 'annotations',
        'k' => 'binaryAnnotations',
        'l' => 'timestamp',
        'm' => 'duration',
        'n' => 'tags',
        'o' => 'debug',
    );

    const MAP_ENDPOINT = array(
        'a' => 'serviceName',
        'b' => 'ipv4',
        'c' => 'ipv6',
        'd' => 'port',
    );

    const MAP_ANNOTATION = array(
        'a' => 'timestamp',
        'b' => 'value',
        'c' => 'endpoint',
    );

    const MAP_BINARYANNOTATION = array(
        'a' => 'key',
        'b' => 'value',
        'c' => 'type',
        'd' => 'endpoint',
    );

    const ENDPOINT_STRUCTURE = self::ENDPOINT_MAP;

    const ANNOTATION_STRUCTURE = array(
        'timestamp' => 'a',
        'value' => 'b',
        'endpoint' => self::ENDPOINT_STRUCTURE,
    );

    const BINARYANNOTATION_STRUCTURE = array(
        'key' => 'a',
        'value' => 'b',
        'type' => 'c',
        'endpoint' => self::ENDPOINT_STRUCTURE,
    );

    const SPAN_STRUCTURE = array(
        'traceId' => 'a',
        'name' => 'b',
        'id' => 'c',
        'parentId' => 'd',
        'decision' => 'e',
        'localEndpoint' => self::ENDPOINT_STRUCTURE,
        'kind' => 'g',
        'shared' => 'h',
        'remoteEndpoint' => self::ENDPOINT_STRUCTURE,
        'annotations' => array(
            self::ANNOTATION_STRUCTURE
        ),
        'binaryAnnotations' => array(
            self::BINARYANNOTATION_STRUCTURE
        ),
        'timestamp' => 'l',
        'duration' => 'm',
        'tags' => 'n',
        'debug' => 'o',
    );

    /**
     * @param $spans
     * @param $gz bool gz compress ?
     * @return string
     */
    public static function spansCompress($spans, $gz = true)
    {
        foreach ($spans as $k => $span) {
            $span = self::shiftNull($span);
            $spans[$k] = Core\Span::shorten($span);
        }
        $inflated = json_encode($spans);
        return $gz ? self::deflate($inflated) : $inflated;
    }

    /**
     * @param $compressed
     * @param $gz bool gz compress ?
     * @return mixed
     */
    public static function spansUnCompress($compressed, $gz = true)
    {
        $inflated = $gz ? self::inflate($compressed) : $compressed;
        $spans = json_decode($inflated, 1);
        foreach ($spans as $k => $shorten_span) {
            $span = Core\Span::normalize($shorten_span);
            $spans[$k] = self::unshiftNull($span, self::SPAN_STRUCTURE);
        }
        return $spans;
    }

    /**
     * @param $arr
     * @param $map
     * @return array
     */
    public static function map($arr, $map)
    {
        $new = array();
        foreach ($arr as $key => $val) {
            $new[$map[$key]] = $val;
        }
        return $new;
    }

    /**
     * @param $str
     * @return string
     */
    private static function deflate($str)
    {
        return gzdeflate($str);
    }

    /**
     * @param $deflated
     * @return string
     */
    private static function inflate($deflated)
    {
        return gzinflate($deflated);
    }

    /**
     * @param $arr
     * @return array
     */
    private static function shiftNull($arr)
    {
        $new_arr = array();
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $new_arr[$key] = self::shiftNull($val);
            } else if (!is_null($val)) {
                $new_arr[$key] = $val;
            }
        }
        return $new_arr;
    }

    /**
     * @param $arr
     * @param $structure
     * @return mixed
     */
    private static function unshiftNull($arr, $structure)
    {
        foreach ($structure as $key => $val) {
            if (!isset($arr[$key])) {
                $arr[$key] = null;
            } else if (isset($val[0]) && is_array($val[0])) {
                // two-dimensional array
                foreach ($arr[$key] as $k => $v) {
                    $arr[$key][$k] = self::unshiftNull($arr[$key], $val[0]);
                }
            } else if (is_array($val)) {
                $arr[$key] = self::unshiftNull($arr[$key], $val);
            }
        }
        return $arr;
    }
}