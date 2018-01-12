<?php
/**
 * User: Tricolor
 * Date: 2017/11/4
 * Time: 20:41
 */
namespace Tricolor\ZTracker;
use Tricolor\ZTracker\Carrier\HttpHeaders;
use Tricolor\ZTracker\Carrier\RabbitMQHeaders;
use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Context;
use Tricolor\ZTracker\Core\Decision;
use Tricolor\ZTracker\Core\Endpoint;
use Tricolor\ZTracker\Core\Span;

class GlobalTracer
{

    /**
     * @return Core\Context
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
        return (new Span())->timestamp(Util::current());
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
     * @param $carrier
     * @return null|HttpHeaders|RabbitMQHeaders
     */
    public static function newCarrier($carrier)
    {
        switch ($carrier) {
            case Carrier\CarrierType\HttpHeader:
                return new HttpHeaders();
            case Carrier\CarrierType\RabbitMQHeader:
                return new RabbitMQHeaders();
        }
        return null;
    }

    /**
     * @return Endpoint
     */
    public static function endpointBuilder()
    {
        return new Endpoint();
    }

}