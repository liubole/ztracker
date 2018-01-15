<?php
/**
 * User: Tricolor
 * Date: 2017/11/4
 * Time: 20:41
 */
namespace Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Common\Util;

class GlobalTracer
{
    /**
     * @return SimpleTracer
     */
    public static function tracer()
    {
        return SimpleTracer::getInstance();
    }

    /**
     * @return Context
     */
    public static function contextBuilder()
    {
        return new Context();
    }

    /**
     * @return Span
     */
    public static function spanBuilder()
    {
        $span = new Span();
        return $span->timestamp(Util::current());
    }

    /**
     * @param null $value
     * @return Decision
     */
    public static function decisionBuilder($value = null)
    {
        return new Decision($value);
    }

    /**
     * @return Endpoint
     */
    public static function endpointBuilder()
    {
        return new Endpoint();
    }

}