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
    private static $prefix = 'Z1-';
    /**
     * @var \PhpAmqpLib\Message\AMQPMessage
     */
    private $msg;
    private $context;
    private $span;

    public function __construct()
    {
    }

    /**
     * @param $msg
     * @return RabbitMQHeaders
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
    public function span(Core\Span $span)
    {
        $this->span = $span;
        return $this;
    }

    /**
     * @param $context Core\Context
     * @return RabbitMQHeaders
     */
    public function context(Core\Context $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return RabbitMQHeaders
     */
    public function inject()
    {
        // span
        $span = array();
        $prefix = self::$prefix . "S-";
        $span[$prefix . "TraceId"] = $this->span->traceId;
        $span[$prefix . "SpanId"] = $this->span->id;
        $span[$prefix . "ParentId"] = $this->span->parentId;
        $span[$prefix . "Decision"] = $this->span->decision->getValue();

        // context
        $context = array();
        $prefix = self::$prefix . "C-";
        foreach(get_object_vars($this->context) as $key => $val) {
            $context[$prefix . $key] =  $val;
        }
        $appends = array_merge($span, $context);

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
     * @return RabbitMQHeaders
     */
    public function extract()
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

        $context = $span = array();
        foreach ($headers as $key => $val) {
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