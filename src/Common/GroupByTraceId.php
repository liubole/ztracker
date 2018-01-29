<?php
/**
 * User: Tricolor
 * Date: 2018/1/18
 * Time: 15:01
 */
namespace Tricolor\ZTracker\Common;

class GroupByTraceId
{
    /**
     * @param $span_l
     * @param $span_r
     * @return int
     */
    private static function compareTo($span_l, $span_r)
    {
        if ($span_l == $span_r) return 0;
        $x = $span_l['timestamp'] == null ? PHP_INT_MIN : $span_l['timestamp'];
        $y = $span_r['timestamp'] == null ? PHP_INT_MIN : $span_r['timestamp'];
        $byTimestamp = $x < $y ? -1 : ($x == $y ? 0 : 1);
        if ($byTimestamp != 0) return $byTimestamp;
        return substr_compare($span_l['name'], $span_r['name'], 0);
    }

    /**
     * @param $spans
     * @return mixed
     */
    public static function apply($spans)
    {
        $groupedByTraceId = array();
        foreach ($spans as $span) {
            $traceIdKey = ':>' . $span['traceId'];
            if (!isset($groupedByTraceId[$traceIdKey])) {
                $groupedByTraceId[$traceIdKey] = array();
            }
            self::insertSpan($groupedByTraceId[$traceIdKey], $span);
        }
        return array_values($groupedByTraceId);
    }

    /**
     * @param $array
     * @param $insert
     */
    public static function insertSpan(&$array, $insert)
    {
        array_push($array, $insert);
        for ($m = count($array) - 1, $j = $m - 1; $j >= 0 && self::compareTo($array[$j], $array[$m]) > 0; $j--) {
            $tmp = $array[$j];
            $array[$j] = $array[$m];
            $array[$m] = $tmp;
            $m--;
        }
    }
}