<?php
/**
 * User: Tricolor
 * Date: 2017/11/4
 * Time: 20:41
 */
namespace Tricolor\ZTracker\Server;
use Tricolor\ZTracker\Carrier\HttpHeaders;
use Tricolor\ZTracker\Carrier\RabbitMQHeaders;
use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Carrier\CarrierType;

class GlobalTracer
{

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
            case  CarrierType\HttpHeader:
                return new HttpHeaders();
            case CarrierType\RabbitMQHeader:
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