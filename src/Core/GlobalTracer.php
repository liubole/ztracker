<?php
/**
 * User: Tricolor
 * Date: 2017/11/4
 * Time: 20:41
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Core;

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
     * @return Core\Builder\SpanBuilder
     */
    public static function spanBuilder()
    {
        $span = new Core\Builder\SpanBuilder();
        return $span->timestamp(Common\Util::current());
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