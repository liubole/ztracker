<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:17
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common;

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

    public function __construct()
    {
    }

    /**
     * @param $serviceName string
     * @param $ipv4 int
     * @param $ipv6 string
     * @param $port int
     * @return Endpoint
     */
    public static function create($serviceName, $ipv4, $ipv6, $port)
    {
        $endpoint = new Endpoint();
        $endpoint->serviceName =
            empty(Common\Util::checkNotNull($serviceName, "serviceName"))
            ? ""
            : strtolower($serviceName);
        $endpoint->ipv4 = $ipv4;
        $endpoint->ipv6 = $ipv6;
        $endpoint->port = $port;
        return $endpoint;
    }

    /**
     * @see Endpoint#serviceName
     * @param $serviceName String
     * @return $this
     */
    public function serviceName($serviceName)
    {
        $this->serviceName = strtolower($serviceName);
        return $this;
    }
    /**
     * Chaining variant of {@link #parseIp(InetAddress)}
     * @param $addr
     * @return $this
     */
    public function ip($addr)
    {
        $this->parseIp($addr);
        return $this;
    }

    public function parseIp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->ipv6($ip);
        } else {
            $this->ipv4($ip);
        }
    }

    /**
     * @see Endpoint#ipv4
     * @param $ipv4
     * @return $this
     */
    public function ipv4($ipv4)
    {
        $this->ipv4 = $ipv4;
        return $this;
    }

    /**
     * When not null, this sets the {@link Endpoint#ipv6}, unless the input is an <a
     * href="https://tools.ietf.org/html/rfc4291#section-2.5.5.2">IPv4-Compatible or IPv4-Mapped
     * Embedded IPv6 Address</a>. In such case, {@link #ipv4(int)} is called with the embedded
     * address.
     *
     * @see Endpoint#ipv6
     * @param $ipv6 => byte[]
     * @return $this
     */
    public function ipv6($ipv6)
    {
        if (empty($ipv6)) {
            $this->ipv6 = null;
            return $this;
        }
        Common\Util::checkArgument(strlen($ipv6) == 16, "ipv6 addresses are 16 bytes: " . strlen($ipv6));
        $this->ipv6 = $ipv6;
        return $this;
    }

    /**
     * Use this to set the port to an externally defined value.
     *
     * <p>Don't pass {@link Endpoint#port} to this method, as it may result in a
     * NullPointerException. Instead, use {@link Endpoint#toBuilder()} or {@link #port(Short)}.
     *
     * @see Endpoint#port
     * @param $port int  associated with the endpoint. zero coerces to null (unknown)
     * @return $this
     */
    public function port($port)
    {
        Common\Util::checkArgument($port <= 0xffff, "invalid port %s", $port);
        $this->port = $port <= 0 ? null : ($port & 0xffff);
        return $this;
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
        return Common\JsonCodec::write($this);
    }

    /**
     * @param $vars
     * @return Endpoint
     */
    public static function revertFromArray($vars)
    {
        if (!isset($vars)) {
            return null;
        }
        return self::create($vars['serviceName'], $vars['ipv4'], $vars['ipv6'], $vars['port']);
    }

    /**
     * @return array
     */
    public function convertToArray()
    {
        return get_object_vars($this);
    }

    /**
     * @param $vars
     * @return array
     */
    public static function shorten($vars)
    {
        if (!isset($vars)) return null;
        return Common\Compress::map($vars, Common\Compress::ENDPOINT_MAP);
    }

    /**
     * @param $shorten
     * @return array
     */
    public static function normalize($shorten)
    {
        return Common\Compress::map($shorten, Common\Compress::MAP_ENDPOINT);
    }
}