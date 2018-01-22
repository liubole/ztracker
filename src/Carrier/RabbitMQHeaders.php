<?php
/**
 * User: Tricolor
 * Date: 2017/12/20
 * Time: 9:33
 */
namespace Tricolor\ZTracker\Carrier;

use PhpAmqpLib\Wire\AMQPTable;
use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Core;

class RabbitMQHeaders implements Base
{
    private static $prefix = 'Tr-';
    /**
     * @var \PhpAmqpLib\Message\AMQPMessage
     */
    private $msg;
    private $context;
    /**
     * @var Core\Builder\SpanBuilder
     */
    private $span;

    public function __construct()
    {
    }

    /**
     * @param $msg
     * @return null|RabbitMQHeaders
     */
    public function pipe(&$msg)
    {
        $this->msg = &$msg;
        return $this;
    }

    /**
     *
     * @param Core\Span $span
     * @return RabbitMQHeaders
     */
    public function span(Core\Span &$span)
    {
        if (!is_null($span)) {
            $this->span = &$span;
        }
        return $this;
    }

    /**
     * @param $context Core\Context
     * @return RabbitMQHeaders
     */
    public function context(Core\Context &$context)
    {
        $this->context = &$context;
        return $this;
    }

    /**
     * @return RabbitMQHeaders
     */
    public function inject()
    {
        $appends = array();
        // span
        if (isset($this->span)) {
            $span = array();
            $prefix = self::$prefix . "S-";
            $span[$prefix . "TraceId"] = $this->span->traceId;
            $span[$prefix . "SpanId"] = $this->span->id;
            $span[$prefix . "ParentId"] = $this->span->parentId;
            $span[$prefix . "Decision"] = $this->span->decision->getValue();
            $appends = array_merge($appends, $span);
        }
        // context
        if (isset($this->context)) {
            $context = array();
            $prefix = self::$prefix . "C-";
            foreach (get_object_vars($this->context) as $key => $val) {
                $context[$prefix . $key] = $val;
            }
            $appends = array_merge($appends, $context);
        }

        // inject
        try {
            $hdr = $this->msg->get('application_headers');
        } catch (\Exception $e) {
            $hdr = new AMQPTable();
        }
        foreach ($appends as $key => $val) {
            $hdr->set($key, $val, AMQPTable::T_STRING_LONG);
        }
        $this->msg->set('application_headers', $hdr);

        return $this;
    }

    /**
     * @param Core\Builder\SpanBuilder $span
     * @param Core\Context|null $context
     * @return null|RabbitMQHeaders
     */
    public function extract(Core\Builder\SpanBuilder &$span = null, Core\Context &$context = null)
    {
        try {
            $hdr = $this->msg->get('application_headers');
            $headers = $hdr->getNativeData();
            if (!$headers) {
                return $this;
            }
        } catch (\Exception $e) {
            return $this;
        }

        $ctx_array = $span_array = array();
        foreach ($headers as $key => $val) {
            if (Common\Util::startsWith($key, $prefix = self::$prefix . "C-")) {
                $ctx_array[substr($key, strlen($prefix))] = $val;
            } else if (Common\Util::startsWith($key, $prefix = self::$prefix . "S-")) {
                $span_array[substr($key, strlen($prefix))] = $val;
            }
        }
        $completion = isset($span_array['TraceId']) && isset($span_array['SpanId']);
        if ($completion) {
            $builder = Core\GlobalTracer::spanBuilder()
                ->traceId($span_array['TraceId'])
                ->id($span_array['SpanId'])
                ->parentId($span_array['ParentId'])
                ->decision($span_array['Decision']);
            if (isset($span)) {
                $span = $builder;
            }
            $this->span($builder->build());
        }
        $ctx = new Core\Context();
        foreach ($ctx_array as $k => $v) $ctx->set($k, $v);
        $this->context($ctx);
        if (isset($context)) {
            $context = $this->context;
        }

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
     * @return Core\Builder\SpanBuilder
     */
    public function getSpan()
    {
        return $this->span;
    }
}