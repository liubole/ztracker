<?php
/**
 * User: Tricolor
 * Date: 2017/12/20
 * Time: 9:32
 */
namespace Tricolor\ZTracker\Carrier;

use Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Common;

class HttpHeaders implements Base
{
    private static $prefix = 'Tr-';
    private $headers;
    private $context;
    private $span;

    public function __construct()
    {
    }

    /**
     * @param $headers
     * @return HttpHeaders
     */
    public function pipe(&$headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     *
     * @param Core\Span $span
     * @return $this
     */
    public function span(Core\Span $span)
    {
        $this->span = $span;
        return $this;
    }

    /**
     * @param $context Core\Context
     * @return HttpHeaders
     */
    public function context(Core\Context $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return HttpHeaders
     */
    public function inject()
    {
        // span
        $span = array();
        $prefix = self::$prefix . "S-";
        $span[] = $prefix . "TraceId: " . $this->span->traceId;
        $span[] = $prefix . "SpanId: " . $this->span->id;
        $span[] = $prefix . "ParentId: " . $this->span->parentId;
        $span[] = $prefix . "Decision: " . $this->span->decision->getValue();

        // context
        $context = array();
        $prefix = self::$prefix . "C-";
        foreach(get_object_vars($this->context) as $key => $val) {
            $context[] = $prefix . $key . ": " . $val;
        }
        $appends = array_merge($span, $context);

        // inject
        foreach ($appends as $v) {
            $this->headers[] = $v;
        }

        return $this;
    }

    /**
     * @return HttpHeaders
     */
    public function extract()
    {
        $context = $span = array();
        foreach ($this->headers as $key => $val) {
            if (Common\Util::startsWith($key, $prefix = self::$prefix . "C-")) {
                $context[substr($key, strlen($prefix))] = $val;
            } else if (Common\Util::startsWith($key, $prefix = self::$prefix . "S-")) {
                $span[substr($key, strlen($prefix))] = $val;
            }
        }
        $this->span = Core\GlobalTracer::spanBuilder()
            ->traceId($span['TraceId'])
            ->id($span['SpanId'])
            ->parentId($span['ParentId'])
            ->decision(Core\GlobalTracer::decisionBuilder($span['Decision']));
        $this->context = new Core\Context();
        foreach ($context as $k => $v) { $this->context->set($k, $v);}
        return $this;
    }

    /**
     * @return Core\Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Core\Span
     */
    public function getSpan()
    {
        return $this->span;
    }

}