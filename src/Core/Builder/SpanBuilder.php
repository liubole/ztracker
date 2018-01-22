<?php
/**
 * User: Tricolor
 * Date: 2018/1/22
 * Time: 16:04
 */
namespace Tricolor\ZTracker\Core\Builder;

use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Exception\NullPointerException;

class SpanBuilder
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
     * @var Core\Decision
     */
    public $decision;
    /**
     * @var Core\Endpoint
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
     * @var Core\Endpoint
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

    /**
     * @see Span#name
     * @param $name
     * @return SpanBuilder
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
     * @return SpanBuilder
     */
    public function traceId($traceId)
    {
        $this->traceId = $traceId;
        return $this;
    }

    /**
     * @see Span#id
     * @param $id
     * @return SpanBuilder
     */
    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @see Span#parentId
     * @param $parentId
     * @return SpanBuilder
     */
    public function parentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @see Span#timestamp
     * @param $timestamp
     * @return SpanBuilder
     */
    public function timestamp($timestamp)
    {
        $this->timestamp = $timestamp != null && $timestamp == 0 ? null : $timestamp;
        return $this;
    }

    /**
     * @see Span#duration
     * @param $duration
     * @return SpanBuilder
     */
    public function duration($duration)
    {
        $this->duration = $duration != null && $duration == 0 ? null : $duration;
        return $this;
    }

    /**
     * @param Core\Decision $decision
     * @return SpanBuilder
     */
    public function decision(Core\Decision $decision)
    {
        $this->decision = $decision;
        return $this;
    }

    /**
     * @param $kind
     * @return SpanBuilder
     */
    public function kind($kind)
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * @see Span#localEndpoint
     * @param Core\Endpoint $localEndpoint
     * @return SpanBuilder
     */
    public function localEndpoint(Core\Endpoint $localEndpoint)
    {
        $this->localEndpoint = $localEndpoint;
        return $this;
    }

    /**
     * @see Span#remoteEndpoint
     * @param Core\Endpoint $remoteEndpoint
     * @return SpanBuilder
     */
    public function remoteEndpoint(Core\Endpoint $remoteEndpoint)
    {
        $this->remoteEndpoint = $remoteEndpoint;
        return $this;
    }

    /**
     * @see Span#tags
     * @param $key
     * @param $value
     * @return SpanBuilder
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
     * @return Core\Builder\SpanBuilder
     */
    public function end()
    {
        $this->duration(Common\Util::duration($this->timestamp, Common\Util::current()));
        return $this;
    }

    /**
     * @see Span#debug
     * @param $debug
     * @return SpanBuilder
     */
    public function debug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param Core\Annotation $annotation
     * @return $this
     */
    public function addAnnotation(Core\Annotation $annotation)
    {
        isset($this->annotations) OR ($this->annotations = array());
        if ($annotation instanceof Core\Annotation) {
            $this->annotations[] = $annotation;
        }
        return $this;
    }

    /**
     * @see Span#binaryAnnotations
     * @param Core\BinaryAnnotation $binaryAnnotation
     * @return $this
     */
    public function addBinaryAnnotation(Core\BinaryAnnotation $binaryAnnotation)
    {
        isset($this->binaryAnnotations) OR ($this->binaryAnnotations = array());
        if ($binaryAnnotation instanceof Core\BinaryAnnotation) {
            $this->binaryAnnotations[] = $binaryAnnotation;
        }
        return $this;
    }

    public function build()
    {
        return Core\Span::create($this);
    }

}