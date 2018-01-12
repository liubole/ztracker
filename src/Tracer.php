<?php
/**
 * User: Tricolor
 * Date: 2018/1/11
 * Time: 9:34
 */
namespace Tricolor\ZTracker;

use Tricolor\ZTracker\Core\Collector;
use Tricolor\ZTracker\Core\Context;
use Tricolor\ZTracker\Core\Endpoint;
use Tricolor\ZTracker\Core\Span;

class Tracer
{
    private static $span;
    private static $localEndpoint;
    private static $context;
    private static $reportSpans;
    private static $logs;

    /**
     * @param Span|null $span
     * @return Span
     */
    public static function span(Span $span = null)
    {
        if ($span) {
            self::$span = $span;
        }
        return self::$span;
    }

    /**
     * @param Endpoint|null $localEndpoint
     * @return Endpoint
     */
    public static function localEndpoint(Endpoint $localEndpoint = null)
    {
        if ($localEndpoint) {
            self::$localEndpoint = $localEndpoint;
        }
        return self::$localEndpoint;
    }

    /**
     * @param Context|null $context
     * @return Context
     */
    public static function context(Context $context = null)
    {
        if ($context) {
            self::$context = $context;
        }
        return self::$context;
    }

    public static function joinSpans(Span $span)
    {
        //加入到要上报的span列表中
        isset(self::$reportSpans) OR (self::$reportSpans = array());
        self::$reportSpans = array_merge(self::$reportSpans, func_get_args());
    }

    public static function log($key, $value = null)
    {
        if (func_num_args() > 1) {
            self::$logs[$key] = $value;
        } else {
            self::$logs[] = $key;
        }
    }

    /**
     *
     */
    public static function flush()
    {
        if (!self::$reportSpans) {
            return;
        }
        foreach (self::$reportSpans as &$span) {
            $span = $span->getToReport();
        }
        $reportOn = self::span()->decision
            ? self::span()->decision->reportOn()
            : true;
        $logOn = self::span()->decision
            ? self::span()->decision->logOn()
            : true;
        $logs = $spans = null;
        if ($reportOn && self::$reportSpans) {
            $spans =& self::$reportSpans;
        }
        if ($logOn && self::$logs) {
            $associate = self::span()
                ? array('traceId' => self::span()->traceId, 'spanId' => self::span()->id,)
                : array();
            $logs = array_merge($associate, self::$logs);
        }
        Collector::collect($spans, $logs);
    }
}