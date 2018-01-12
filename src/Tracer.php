<?php
/**
 * User: Tricolor
 * Date: 2018/1/11
 * Time: 9:34
 */
namespace Tricolor\ZTracker;

use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Context;
use Tricolor\ZTracker\Core\Endpoint;
use Tricolor\ZTracker\Core\Span;

class Tracer
{
    private static $span;
    private static $localEndpoint;
    private static $context;
    private static $reportSpans;

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

    /**
     */
    public static function flush()
    {
        if (!self::$reportSpans) {return;}
        foreach (self::$reportSpans as &$span) {
            if (!isset($span->duration)) {
                $span->duration = Util::duration($span->timestamp, Util::current());
            }
            if ($span->decision) {
                $span->decision = $span->decision->toArray();
            }
            if ($span->localEndpoint) {
                $span->localEndpoint = $span->localEndpoint->toArray();
            }
            if ($span->remoteEndpoint) {
                $span->remoteEndpoint = $span->remoteEndpoint->toArray();
            }
            foreach ($span->annotations as &$annotation) {
                $annotation = $annotation->toArray();
            }
        }
        // 记录span
        // todo
        var_dump(self::$reportSpans);
    }
}