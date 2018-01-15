<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:15
 */
namespace Tricolor\ZTracker\Server;

use Tricolor\ZTracker\Common\Util;

class Annotation
{
    /**
     * Microseconds from epoch.
     *
     * <p>This value should be set directly by instrumentation, using the most precise value possible.
     * For example, {@code gettimeofday} or multiplying {@link System#currentTimeMillis} by 1000.
     * long
     */
    public $timestamp;

    /**
     * Usually a short tag indicating an event, like {@link Constants#SERVER_RECV "sr"}. or {@link
     * Constants#ERROR "error"}
     * @var string
     */
    public $value;

    /**
     * The host that recorded {@link #value}, primarily for query by service name.
     * @var Endpoint
     */
    public $endpoint;

    public function __construct()
    {
    }

    public static function create($timestamp, $value, Endpoint $endpoint)
    {
        return new Annotation($timestamp, $value, $endpoint);
    }

    /**
     * @see Annotation#timestamp
     * @param $timestamp
     * @return $this
     */
    public function timestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @see Annotation#value
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @see Annotation#endpoint
     * @param Endpoint $endpoint
     * @return $this
     */
    public function endpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function equals($o)
    {
        if ($o instanceof Annotation) {
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

    /**
     * Compares by {@link #timestamp}, then {@link #value}.
     * @param Annotation
     * @return int
     */
    public function compareTo(Annotation $that)
    {
        if ($this == $that) return 0;
        $byTimestamp = $this->timestamp < $that->timestamp ? -1 : $this->timestamp == $that->timestamp ? 0 : 1;
        if ($byTimestamp != 0) return $byTimestamp;
        return substr_compare($this->value, $that->value, 0);
    }

    public function convertToArray()
    {
        return get_object_vars($this);
    }
}