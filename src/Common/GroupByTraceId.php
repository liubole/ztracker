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
        $byTimestamp = $x < $y ? -1 : $x == $y ? 0 : 1;
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
            $traceId = $span['traceId'];
            if (!isset($groupedByTraceId[$traceId])) {
                $groupedByTraceId[$traceId] = array();
            }
            $groupedByTraceId[$traceId][] = $span;
        }
        $result = array();
        foreach ($groupedByTraceId as $sameTraceId) {
            $result[] = $sameTraceId;
        }
        return $result;
    }
}