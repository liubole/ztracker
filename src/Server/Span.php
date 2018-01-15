<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 16:32
 */
namespace Tricolor\ZTracker\Server;

use Tricolor\ZTracker\Common\JsonCodec;
use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Exception\NullPointerException;

class Span
{
    /**
     * Long
     */
    public $traceId;
    /**
     * String
     */
    public $name;
    /**
     * Long
     */
    public $id;
    /**
     * Long
     */
    public $parentId;

    /**
     * @var Decision
     */
    public $decision;

    /**
     * @var Endpoint
     */
    public $localEndpoint;

    /**
     * @var
     */
    public $kind;

    /**
     * @var Endpoint
     */
    public $remoteEndpoint;

    /**
     * Long
     */
    public $timestamp;
    /**
     * Long
     */
    public $duration;

    /**
     * @var array<Annotation>
     */
    public $annotations;
    /**
     * @var array
     */
    public $tags;
    /**
     * @var boolean
     */
    public $debug;

    public function __construct()
    {
    }

    public function clear()
    {
        $this->traceId = null;
        $this->parentId = null;
        $this->id = null;
        $this->kind = null;
        $this->name = null;
        $this->timestamp = null;
        $this->duration = null;
        $this->localEndpoint = null;
        $this->remoteEndpoint = null;
        if ($this->annotations != null) $this->annotations = null;
        if ($this->tags != null) $this->tags = null;
        $this->debug = null;
        return $this;
    }

    public function copy()
    {
        $result = new Span();
        $result->traceId = $this->traceId;
        $result->parentId = $this->parentId;
        $result->id = $this->id;
        $result->kind = $this->kind;
        $result->name = $this->name;
        $result->timestamp = $this->timestamp;
        $result->duration = $this->duration;
        $result->localEndpoint = $this->localEndpoint;
        $result->remoteEndpoint = $this->remoteEndpoint;
        if ($this->annotations != null) {
            $result->annotations = (array)$this->annotations;
        }
        if ($this->tags != null) {
            $result->tags = (array)$this->tags;
        }
        $result->debug = $this->debug;
        return $result;
    }

    public function childOf(Span $span)
    {
        $this->traceId($span->traceId)
            ->parentId($span->id)
            ->decision($span->decision)
            ->id(Util::spanId());
        return $this;
    }

    /**
     * @param Span $that
     * @return mixed Builder
     */
    public function merge(Span $that)
    {
        if ($this->traceId == null) {
            $this->traceId = $that->traceId;
        }

        if (($this->name == null) || (strlen($this->name) == 0) || ($this->name == "unknown")) {
            $this->name = $that->name;
        }
        if ($this->id == null) {
            $this->id = $that->id;
        }
        if ($this->parentId == null) {
            $this->parentId = $that->parentId;
        }

        // Single timestamp makes duration easy: just choose max
        if ($this->timestamp == null || $that->timestamp == null || $this->timestamp == $that->timestamp) {
            $this->timestamp = $this->timestamp != null ? $this->timestamp : $that->timestamp;
            if ($this->duration == null) {
                $this->duration = $that->duration;
            } else if ($that->duration != null) {
                $this->duration = max($this->duration, $that->duration);
            }
        } else { // duration might need to be recalculated, since we have 2 different timestamps
            $thisEndTs = $this->duration != null ? $this->timestamp + $this->duration : $this->timestamp;
            $thatEndTs = $that->duration != null ? $that->timestamp + $that->duration : $that->timestamp;
            $this->timestamp = min($this->timestamp, $that->timestamp);
            $this->duration = max($thisEndTs, $thatEndTs) - $this->timestamp;
        }

        foreach ($that->annotations as $a) {
            $this->addAnnotation($a);
        }
        if ($this->debug == null) {
            $this->debug = $that->debug;
        }
        return $this;
    }

    /**
     * @see Span#name
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function shared($shared)
    {
        $this->shared = $shared;
        return $this;
    }

    /**
     * @see Span#traceId
     * @param $traceId
     * @return $this
     */
    public function traceId($traceId)
    {
        $this->traceId = $traceId;
        return $this;
    }

    /**
     * @see Span#id
     * @param $id
     * @return $this
     */
    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @see Span#parentId
     * @param $parentId
     * @return $this
     */
    public function parentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @see Span#timestamp
     * @param $timestamp
     * @return $this
     */
    public function timestamp($timestamp)
    {
        $this->timestamp = $timestamp != null && $timestamp == 0 ? null : $timestamp;
        return $this;
    }

    /**
     * @see Span#duration
     * @param $duration
     * @return $this
     */
    public function duration($duration)
    {
        $this->duration = $duration != null && $duration == 0 ? null : $duration;
        return $this;
    }

    /**
     * @param Decision $decision
     * @return $this
     */
    public function decision(Decision $decision)
    {
        $this->decision = $decision;
        return $this;
    }

    /**
     * @param $kind
     * @return $this
     */
    public function kind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * @see Span#localEndpoint
     * @param Endpoint $localEndpoint
     * @return $this
     */
    public function localEndpoint(Endpoint $localEndpoint)
    {
        $this->localEndpoint = $localEndpoint;
        return $this;
    }

    /**
     * @see Span#remoteEndpoint
     * @param Endpoint $remoteEndpoint
     * @return $this
     */
    public function remoteEndpoint(Endpoint $remoteEndpoint)
    {
        $this->remoteEndpoint = $remoteEndpoint;
        return $this;
    }

    /**
     * @see Span#tags
     * @param $key
     * @param $value
     * @return $this
     * @throws NullPointerException
     */
    public function putTag($key, $value)
    {
        if ($this->tags == null) $this->tags = array();
        if (is_null($key)) throw new NullPointerException("key == null");
        if (is_null($value)) throw new NullPointerException("value of " . $key . " == null");
        $this->tags[$key] = $value;
        return $this;
    }

    /**
     * @see Span#debug
     * @param $debug
     * @return $this
     */
    public function debug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Replaces currently collected annotations.
     *
     * @see Span#annotations
     * @param array $annotations
     * @return $this
     */
    public function annotations(array $annotations)
    {
        $this->annotations = $annotations;
        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return JsonCodec::write($this);
    }

    /**
     * @param $o
     * @return bool
     */
    public function equals($o)
    {

        if ($o instanceof Span) {
            return $o == $this;
        }
        if (is_array($o)) {
            return $o == get_object_vars($this);
        }
        return false;
    }

    /**
     * @return string
     */
    public function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     * @see Span#annotations
     * @param $value string Annotation.value
     * @param $timestamp string Annotation.timestamp
     * @return $this
     */
    public function addAnnotation($value, $timestamp = null)
    {
        if ($this->annotations == null) {
            $this->annotations = array();
        }
        $timestamp = $timestamp ? $timestamp : Util::current();
        $annotation = new Annotation();
        $annotation = $annotation->timestamp($timestamp)->value((string)$value);
        array_push($this->annotations, $annotation);
        return $this;
    }

    /**
     * Compares by {@link #timestamp}, then {@link #name}.
     * @param Span $that
     * @return int
     */
    public function compareTo(Span $that)
    {
        if ($this == $that) return 0;
        $x = $this->timestamp == null ? PHP_INT_MIN : $this->timestamp;
        $y = $that->timestamp == null ? PHP_INT_MIN : $that->timestamp;
        $byTimestamp = $x < $y ? -1 : $x == $y ? 0 : 1;
        if ($byTimestamp != 0) return $byTimestamp;
        return substr_compare($this->name, $that->name, 0);
    }

    /**
     * Returns {@code $traceId.$spanId<:$parentId}
     * @return string
     */
    public function idString()
    {
        return $this->traceId . '.' . $this->id . '<:' . $this->parentId;
    }

    /**
     * Returns the distinct {@link Endpoint#serviceName service names} that logged to this span.
     * Set<String>
     * @return array
     */
    public function serviceNames()
    {
        $result = array();
        foreach ($this->annotations as $a) {
            if ($a->endpoint == null) continue;
            if (empty($a->endpoint->serviceName)) continue;
            array_push($result, $a->endpoint->serviceName);
        }
        foreach ($this->binaryAnnotations as $a) {
            if ($a->endpoint == null) continue;
            if (empty($a->endpoint->serviceName)) continue;
            array_push($result, $a->endpoint->serviceName);
        }
        return array_unique($result);
    }

    public function getToReport()
    {
        $array = array();
        if (!isset($this->duration)) {
            $this->duration = Util::duration($this->timestamp, Util::current());
        }
        foreach (get_object_vars($this) as $key => $val) {
            if ($val instanceof Decision) {
                $array[$key] = $val->decision;
            } else if ($val instanceof Endpoint) {
                $array[$key] = $val->convertToArray();
            } else if (is_array($val) && count($val) > 0) {
                foreach ($val as $k => $v) {
                    if ($v instanceof Annotation) {
                        $array[$key][$k] = $v->convertToArray();
                    } else {
                        $array[$key][$k] = $v;
                    }
                }
            } else {
                $array[$key] = $val;
            }
        }
        return $array;
    }
}