<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 9:29
 */
namespace Tricolor\ZTracker\Core\Builder;

use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Endpoint;

class EndpointBuilder
{
    /**
     * @var => String
     */
    public $serviceName;
    /**
     * @var => Integer
     */
    public $ipv4;
    /**
     * @var => byte[]
     */
    public $ipv6;
    /**
     * @var => Short
     */
    public $port;

    public function __construct(Endpoint $source = null)
    {
        if (!$source) {
            return;
        }
        $this->serviceName = $source->serviceName;
        $this->ipv4 = $source->ipv4;
        $this->ipv6 = $source->ipv6;
        $this->port = $source->port;
    }

    /**
     * @see Endpoint#serviceName
     * @param $serviceName String
     * @return $this
     */
    public function serviceName($serviceName)
    {
        $this->serviceName = $serviceName;
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
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $this->ipv6($ip);
        } else {
            $this->ipv4($ip);
        }
    }

    /** @see Endpoint#ipv4 */
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
        Util::checkArgument(strlen($ipv6) == 16, "ipv6 addresses are 16 bytes: " . strlen($ipv6));
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
        Util::checkArgument($port <= 0xffff, "invalid port %s", $port);
        $this->port = $port <= 0 ? null : ($port & 0xffff);
        return $this;
    }

    /**
     * @return Endpoint
     */
    public function build()
    {
        return new Endpoint($this->serviceName, empty($this->ipv4) ? 0 : $this->ipv4, $this->ipv6, $this->port);
    }
}