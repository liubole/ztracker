<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 16:32
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common;
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
     * @var boolean
     */
    public $shared;
    /**
     * @var Endpoint
     */
    public $remoteEndpoint;
    /**
     * @var array
     */
    public $annotations;

    /**
     * @var array
     */
    public $binaryAnnotations;
    /**
     * Long
     */
    public $timestamp;
    /**
     * Long
     */
    public $duration;
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
            ->id(Common\Util::spanId());
        return $this;
    }

    /**
     * @param Span $that
     * @return mixed Builder
     */
    public function merge(Span $that)
    {
        if (!$that) {
            return $this;
        }
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
        if ($that->annotations) {
            foreach ($that->annotations as $a) {
                $this->hasAnnotation($a) OR $this->addAnnotation($a);
            }
        }
        if ($that->tags) {
            foreach ($that->tags as $k => $v) {
                $this->putTag($k, $v);
            }
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
        if ($this->debug == null) {
            $this->debug = $that->debug;
        }
        return $this;
    }

    public function hasAnnotation(Annotation $a)
    {
        if ($this->annotations) {
            foreach ($this->annotations as $v) {
                if ($a == $v) return true;
            }
        }
        return false;
    }

    /**
     * @param $vars
     * @return $this
     */
    public function enrich($vars)
    {
        foreach (array_intersect_key(get_object_vars($this), $vars) as $key => $val) {
            $this->$key = $vars[$key];
        }
        return $this;
    }

    /**
     * @see Span#name
     * @param $name
     * @return Span
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function shared($shared)
    {
        $this->shared = (int)$shared;
        return $this;
    }

    /**
     * @see Span#traceId
     * @param $traceId
     * @return Span
     */
    public function traceId($traceId)
    {
        $this->traceId = $traceId;
        return $this;
    }

    /**
     * @see Span#id
     * @param $id
     * @return Span
     */
    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @see Span#parentId
     * @param $parentId
     * @return Span
     */
    public function parentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @see Span#timestamp
     * @param $timestamp
     * @return Span
     */
    public function timestamp($timestamp)
    {
        $this->timestamp = $timestamp != null && $timestamp == 0 ? null : $timestamp;
        return $this;
    }

    /**
     * @see Span#duration
     * @param $duration
     * @return Span
     */
    public function duration($duration)
    {
        $this->duration = $duration != null && $duration == 0 ? null : $duration;
        return $this;
    }

    /**
     * @param Decision $decision
     * @return Span
     */
    public function decision(Decision $decision)
    {
        $this->decision = $decision;
        return $this;
    }

    /**
     * @param $kind
     * @return Span
     */
    public function kind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * @see Span#localEndpoint
     * @param Endpoint $localEndpoint
     * @return Span
     */
    public function localEndpoint(Endpoint $localEndpoint)
    {
        $this->localEndpoint = $localEndpoint;
        return $this;
    }

    /**
     * @see Span#remoteEndpoint
     * @param Endpoint $remoteEndpoint
     * @return Span
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
     * @return Span
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
     * 
     */
    public function end()
    {
        $this->duration(Common\Util::duration($this->timestamp, Common\Util::current()));
    }

    /**
     * @see Span#debug
     * @param $debug
     * @return Span
     */
    public function debug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param Annotation $annotation
     * @return $this
     */
    public function addAnnotation(Annotation $annotation)
    {
        isset($this->annotations) OR ($this->annotations = array());
        if ($annotation instanceof Annotation) {
            $this->annotations[] = $annotation;
        }
        return $this;
    }

    /**
     * @see Span#binaryAnnotations
     * @param BinaryAnnotation $binaryAnnotation
     * @return $this
     */
    public function addBinaryAnnotation(BinaryAnnotation $binaryAnnotation)
    {
        isset($this->binaryAnnotations) OR ($this->binaryAnnotations = array());
        if ($binaryAnnotation instanceof BinaryAnnotation) {
            $this->binaryAnnotations[] = $binaryAnnotation;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return Common\JsonCodec::write($this);
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
     * @param $vars
     * @return Span
     */
    public static function revertFromArray($vars)
    {
        if (!$vars) {
            return null;
        }
        $span = new Span();
        foreach (array_intersect_key(get_object_vars($span), $vars) as $key => $no_use_val) {
            if ($key == 'decision') {
                $span->$key = Decision::revertFromInt($vars[$key]);
            } else if ($key == 'localEndpoint' || $key == 'remoteEndpoint') {
                $span->$key = Endpoint::revertFromArray($vars[$key]);
            } else if ($key == 'annotations' || $key == 'binaryAnnotations') {
                if (is_array($vars[$key])) {
                    foreach ($vars[$key] as $k => $v) {
                        $span->$key[$k] = Annotation::revertFromArray($v);
                    }
                }
            } else {
                $span->$key = $vars[$key];
            }
        }
        return $span;
    }

    /**
     * @return array
     */
    public function convertToArray()
    {
        $array = array();
        if (!isset($this->duration)) {
            $this->duration = Common\Util::duration($this->timestamp, Common\Util::current());
        }
        foreach (get_object_vars($this) as $key => $val) {
            if ($val instanceof Decision) {
                $array[$key] = $val->convertToInt();
            } else if ($val instanceof Endpoint) {
                $array[$key] = $val->convertToArray();
            } else if (is_array($val) && count($val) > 0) {
                foreach ($val as $k => &$v) {
                    if ($v instanceof Annotation) {
                        $array[$key][$k] = $v->convertToArray();
                    } else if ($v instanceof BinaryAnnotation) {
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

    /**
     * @param $vars
     * @return array
     */
    public static function shorten($vars)
    {
        if (!isset($vars)) return null;
        if (isset($vars['localEndpoint'])) {
            $vars['localEndpoint'] = Endpoint::shorten($vars['localEndpoint']);
        }
        if (isset($vars['remoteEndpoint'])) {
            $vars['remoteEndpoint'] = Endpoint::shorten($vars['remoteEndpoint']);
        }
        if (isset($vars['annotations'])) {
            foreach ($vars['annotations'] as $k => $v) {
                $vars['annotations'][$k] = Annotation::shorten($v);
            }
        }
        if (isset($vars['binaryAnnotations'])) {
            foreach ($vars['binaryAnnotations'] as $k => $v) {
                $vars['binaryAnnotations'][$k] = BinaryAnnotation::shorten($v);
            }
        }
        return Common\Compress::map($vars, Common\Compress::SPAN_MAP);
    }

    /**
     * @param $shorten
     * @return array
     */
    public static function normalize($shorten)
    {
        $map = Common\Compress::SPAN_MAP;
        if (isset($shorten[$map['localEndpoint']])) {
            $shorten[$map['localEndpoint']] = Endpoint::normalize($shorten[$map['localEndpoint']]);
        }
        if (isset($shorten[$map['remoteEndpoint']])) {
            $shorten[$map['remoteEndpoint']] = Endpoint::normalize($shorten[$map['remoteEndpoint']]);
        }
        if (isset($shorten[$map['annotations']])) {
            foreach ($shorten[$map['annotations']] as $k => $v) {
                $shorten[$map['annotations']][$k] = Annotation::normalize($v);
            }
        }
        if (isset($shorten[$map['binaryAnnotations']])) {
            foreach ($shorten[$map['binaryAnnotations']] as $k => $v) {
                $shorten[$map['binaryAnnotations']][$k] = BinaryAnnotation::normalize($v);
            }
        }
        return Common\Compress::map($shorten, Common\Compress::MAP_SPAN);
    }
}