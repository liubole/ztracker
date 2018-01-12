<?php
/**
 * User: Tricolor
 * Date: 2017/12/20
 * Time: 9:32
 */
namespace Tricolor\ZTracker\Carrier;
use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Context;
use Tricolor\ZTracker\Core\Span;

class HttpHeaders
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
     * @param Span $span
     * @return $this
     */
    public function span(Span $span)
    {
        $this->span = $span;
        return $this;
    }

    /**
     * @param $context Context
     * @return HttpHeaders
     */
    public function context(Context $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return HttpHeaders
     */
    public function inject()
    {
        //span
        $span = array();
        $prefix = self::$prefix . "S-";
        $span[] = $prefix . "TraceId: " . $this->span->traceId;
        $span[] = $prefix . "SpanId: " . $this->span->id;
        $span[] = $prefix . "ParentId: " . $this->span->parentId;
        $span[] = $prefix . "Decision: " . $this->span->decision->getValue();

        //context
        $context = array();
        $prefix = self::$prefix . "C-";
        foreach(get_object_vars($this->context) as $key => $val) {
            $context[] = $prefix . $key . ": " . $val;
        }
        $this->headers = array_merge($span, $context);

        return $this;
    }

    /**
     * @return HttpHeaders
     */
    public function extract()
    {
        $context = $span = array();
        foreach ($this->headers as $key => $val) {
            if (Util::startsWith($key, $prefix = self::$prefix . "C-")) {
                $context[substr($key, strlen($prefix))] = $val;
            } else if (Util::startsWith($key, $prefix = self::$prefix . "S-")) {
                $span[substr($key, strlen($prefix))] = $val;
            }
        }
        $this->span = (new Span())
            ->traceId($span['TraceId'])
            ->id($span['SpanId'])
            ->parentId($span['ParentId'])
            ->decision($span['Decision']);
        $this->context = new Context();
        foreach ($context as $k => $v) { $this->context->set($k, $v);}
        return $this;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return mixed
     * @return Context
     */
    public function getSpan()
    {
        return $this->span;
    }

}