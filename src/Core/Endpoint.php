<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:17
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common\JsonCodec;
use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Builder\EndpointBuilder;

class Endpoint
{
    /**
     * Classifier of a source or destination in lowercase, such as "zipkin-server".
     *
     * <p>This is the primary parameter for trace lookup, so should be intuitive as possible, for
     * example, matching names in service discovery.
     *
     * <p>Conventionally, when the service name isn't known, service_name = "unknown". However, it is
     * also permissible to set service_name = "" (empty string). The difference in the latter usage is
     * that the span will not be queryable by service name unless more information is added to the
     * span with non-empty service name, e.g. an additional annotation from the server.
     *
     * <p>Particularly clients may not have a reliable service name at ingest. One approach is to set
     * service_name to "" at ingest, and later assign a better label based on binary annotations, such
     * as user agent.
     * String
     */
    public $serviceName;

    /**
     * IPv4 endpoint address packed into 4 bytes or zero if unknown.
     *
     * <p>Ex for the IP 1.2.3.4, it would be {@code (1 << 24) | (2 << 16) | (3 << 8) | 4}
     *
     * @see java.net.Inet4Address#getAddress()
     * int
     */
    public $ipv4;

    /**
     * IPv6 endpoint address packed into 16 bytes or null if unknown.
     *
     * @see java.net.Inet6Address#getAddress()
     * @since Zipkin 1.4
     * byte[]
     */
    public $ipv6;

    /**
     * Port of the IP's socket or null, if not known.
     *
     * <p>Note: this is to be treated as an unsigned integer, so watch for negatives.
     *
     * @see java.net.InetSocketAddress#getPort()
     * Short
     */
    public $port;

    public function __construct($serviceName, $ipv4, $ipv6, $port)
    {
        $this->serviceName = empty(Util::checkNotNull($serviceName, "serviceName"))
            ? ""
            : strtolower($serviceName);
        $this->ipv4 = $ipv4;
        $this->ipv6 = $ipv6;
        $this->port = $port;
    }

    /**
     * @param $serviceName string
     * @param $ipv4 int
     * @return Endpoint
     */
    public static function create($serviceName, $ipv4)
    {
        return new Endpoint($serviceName, $ipv4, null, null);
    }

    public function toBuilder()
    {
        return new EndpointBuilder($this);
    }

    public static function builder()
    {
        return new EndpointBuilder();
    }

    public function equals($o)
    {
        if ($o instanceof Endpoint) {
            return $o == $this;
        }
        if (is_array($o)) {
            return $o == get_object_vars($this);
        }
        return false;
    }

    public function hashCode()
    {
        return spl_object_hash($this);
    }

    public function toString()
    {
        return JsonCodec::write($this);
    }

    public function convertToArray()
    {
        return get_object_vars($this);
    }
}