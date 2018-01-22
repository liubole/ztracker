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
        $this->headers = &$headers;
        return $this;
    }

    /**
     *
     * @param Core\Span $span
     * @return HttpHeaders
     */
    public function span(Core\Span &$span)
    {
        $this->span = &$span;
        return $this;
    }

    /**
     * @param $context Core\Context
     * @return HttpHeaders
     */
    public function context(Core\Context &$context)
    {
        $this->context = &$context;
        return $this;
    }

    /**
     * @return HttpHeaders
     */
    public function inject()
    {
        $appends = array();
        // span
        if (isset($this->span)) {
            $span = array();
            $prefix = self::$prefix . "S-";
            $span[] = $prefix . "TraceId: " . $this->span->traceId;
            $span[] = $prefix . "SpanId: " . $this->span->id;
            $span[] = $prefix . "ParentId: " . $this->span->parentId;
            $span[] = $prefix . "Decision: " . $this->span->decision->getValue();
            $appends = array_merge($appends, $span);
        }
        // context
        if (isset($this->context)) {
            $context = array();
            $prefix = self::$prefix . "C-";
            foreach(get_object_vars($this->context) as $key => $val) {
                $context[] = $prefix . $key . ": " . $val;
            }
            $appends = array_merge($appends, $context);
        }
        // inject
        foreach ($appends as $v) {
            $this->headers[] = $v;
        }
        return $this;
    }

    /**
     * @param Core\Span|null $span
     * @param Core\Context|null $context
     * @return null|HttpHeaders
     */
    public function extract(Core\Span &$span = null, Core\Context &$context = null)
    {
        $ctx_array = $span_array = array();
        foreach ($this->headers as $key => $val) {
            if (Common\Util::startsWith($key, $prefix = self::$prefix . "C-")) {
                $ctx_array[substr($key, strlen($prefix))] = $val;
            } else if (Common\Util::startsWith($key, $prefix = self::$prefix . "S-")) {
                $span_array[substr($key, strlen($prefix))] = $val;
            }
        }
        $this->span(Core\GlobalTracer::spanBuilder()
            ->traceId($span_array['TraceId'])
            ->id($span_array['SpanId'])
            ->parentId($span_array['ParentId'])
            ->decision($span_array['Decision'])
            ->build()
        );

        $ctx = new Core\Context();
        foreach ($ctx_array as $k => $v) $ctx->set($k, $v);
        $this->context($ctx);

        isset($span) AND ($span = &$this->span);
        isset($context) AND ($context = &$this->context);

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