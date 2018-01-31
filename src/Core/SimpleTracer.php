<?php
/**
 * User: Tricolor
 * Date: 2018/1/13
 * Time: 21:41
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Carrier;
use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Exception\NullPointerException;

class SimpleTracer
{
    /**
     * @var SimpleTracer
     */
    private static $tracer;
    /**
     * @var Span
     */
    private $span;
    /**
     * @var Span
     */
    private $remoteSpan;
    /**
     * @var Endpoint
     */
    private $localEndpoint;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var array
     */
    private $reportSpans;
    /**
     * @var array
     */
    private $logs;

    /**
     * @var Carrier\HttpHeaders|Carrier\RabbitMQHeaders
     */
    private $carrier;

    private function __construct()
    {
        $this->reportSpans = array();
        $this->logs = array();
    }

    /**
     * @return Span
     */
    public function newChildSpan()
    {
        $span = $this->currentSpan();
        $child = GlobalTracer::spanBuilder()
            ->traceId($span->traceId)
            ->id(Common\Util::spanId())
            ->parentId($span->id)
//            ->localEndpoint($this->localEndpoint())
            ->decision($span->decision);
        $this->remoteSpan($child);
        $this->joinSpan($child);
        return $child;
    }

    /**
     * @return Span
     */
    public function newSpan()
    {
        // sample or not
        $decision = GlobalTracer::decisionBuilder();
        $sampled = Common\Util::sampleOrNot(Config\Collector::$sampleRate)
            ? Config\TraceEnv::SAMPLED
            : Config\TraceEnv::NOT_SAMPLED;
        $decision->turn($sampled);
        $span = GlobalTracer::spanBuilder()
            ->traceId(Common\Util::traceId())
            ->id(Common\Util::spanId())
            ->name(Common\Util::getServerApi())
//            ->localEndpoint($this->localEndpoint())
            ->decision($decision);
        $this->currentSpan($span);
        $this->joinSpan($span);
        return $span;
    }

    /**
     * @param Span|null $span
     * @return Span
     */
    public function currentSpan(Span $span = null)
    {
        if ($span) {
            $this->span = $span;
        }
        return $this->span;
    }

    /**
     * @param Span|null $span
     * @return Span
     * @throws NullPointerException
     */
    public function remoteSpan(Span $span = null)
    {
        if ($span) {
            $this->remoteSpan = $span;
        }
        if (!$this->remoteSpan) {
            throw new NullPointerException('Remote span cannot be null!');
        }
        return $this->remoteSpan;
    }

    /**
     * @param Span $span
     */
    public function joinSpan(Span &$span)
    {
        $this->reportSpans[] = $span;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setContext($key, $value)
    {
        $this->currentContext()->set($key, $value);
        return $this;
    }

    /**
     * @param Context|null $context
     * @return Context
     */
    public function currentContext(Context $context = null)
    {
        if ($context) {
            $this->context = $context;
        }
        if (!$this->context) {
            $this->context = GlobalTracer::contextBuilder();
        }
        return $this->context;
    }

    /**
     * @param $type
     * @return SimpleTracer
     */
    public function injector($type)
    {
        switch ($type) {
            case Carrier\CarrierType\HttpHeader:
                $this->carrier = new Carrier\HttpHeaders();
                break;
            case Carrier\CarrierType\RabbitMQHeader:
                $this->carrier = new Carrier\RabbitMQHeaders();
                break;
        }
        return $this;
    }

    /**
     * @param $pipe
     * @return $this
     */
    public function inject(&$pipe)
    {
        $this->carrier
            ->pipe($pipe)
            ->span($this->remoteSpan())
            ->context($this->currentContext())
            ->inject();
        return $this;
    }

    /**
     * @param $pipe
     * @return $this
     */
    public function extract(&$pipe = null)
    {
        if (!isset($pipe) && $this->carrier instanceof Carrier\HttpHeaders) {
            $pipe = Common\Util::getHeaders();
        }
        $this->carrier->pipe($pipe)->extract();
        $content = $this->carrier->getContext();
        $span = $this->carrier->getSpan();

//        $span->localEndpoint($this->localEndpoint());
        $this->currentContext($content);
        $this->currentSpan($span);
        $this->joinSpan($span);
        return $this;
    }

    /**
     * End trace & collect data
     * PS: called behind server-return
     */
    public function flush()
    {
        $span = $this->currentSpan();
        if (($span instanceof Core\Span) && !isset($span->duration)) {
            $span->end();
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            $this->collect();
        } else if (function_exists('register_shutdown_function')) {
            $that = &$this;
            register_shutdown_function(function () use (&$that) {
                $that->collect();
            });
        }
    }

    /**
     * 1.Only when span's decision indicate 'report on' and 'sampled', spans' collection will occurs
     * 2.Logs will be logged only when span's decision indicate 'log on'(record on)
     */
    private function collect()
    {
        // There is nothing to collect
        if (!$this->reportSpans && !$this->logs) {
            return;
        }
        $spans = $logs = array();
        // Spans
        if ($this->reportSpans) {
            $localEndpoint = $this->localEndpoint();
            foreach ($this->reportSpans as &$span) {
                if ($span instanceof Span) {
                    isset($span->localEndpoint) OR ($span->localEndpoint($localEndpoint));
                    $reportOn = $span->decision ? $span->decision->reportOn() : true;
                    $sampled = $span->decision ? $span->decision->sampled() : false;
                    if (!$reportOn || !$sampled || !$span->traceId || !$span->id) continue;
                    array_push($spans, $span->convertToArray());
                }
            }
        }
        // Logs
        $logOn = ($this->currentSpan() && $this->currentSpan()->decision)
            ? $this->currentSpan()->decision->logOn()
            : true;
        if ($logOn && $this->logs) {
            $span = $this->currentSpan();
            $logs = $this->relatedWithSpan($span, $this->logs);
        }
        Reporter::collect($spans, $logs);
    }

    /**
     * @param Endpoint|null $localEndpoint
     * @return Endpoint
     */
    public function localEndpoint(Endpoint $localEndpoint = null)
    {
        if ($localEndpoint) {
            $this->localEndpoint = $localEndpoint;
        }
        if (!$this->localEndpoint) {
            $this->localEndpoint = new Endpoint();
            $this->localEndpoint
                ->serviceName(Common\Util::getServerName())
                ->ip(Common\Util::getServerIp())
                ->port(Common\Util::getServerPort());
        }
        return $this->localEndpoint;
    }

    public function log($key, $value = null)
    {
        if (func_num_args() > 1) {
            $this->logs[$key] = $value;
        } else {
            $this->logs[] = $key;
        }
    }

    /**
     * @param $span
     * @param array $logs
     * @return array
     */
    private function relatedWithSpan(Span &$span, &$logs)
    {
        $traceId = $spanId = $parentId = $timestamp = null;
        if ($span) {
            $traceId = $span->traceId;
            $spanId = $span->id;
            $parentId = $span->parentId;
            $timestamp = $span->timestamp;
        }
        $h = Common\Util::currentInHuman($timestamp);
        $associate = $traceId
            ? array('traceId' => $traceId, 'spanId' => $spanId, 'parentId' => $parentId, 'timestamp' => $h,)
            : array('timestamp' => $h);
        foreach (array_keys($associate) as $key) {
            if (isset($logs[$key])) {
                $logs[$key . '_' . Common\Util::random(6)] = $logs[$key];
                unset($logs[$key]);
            }
        }
        return array_merge($associate, $logs);
    }

    /**
     * @return SimpleTracer
     */
    public static function getInstance()
    {
        if (!self::$tracer) {
            self::$tracer = new self();
        }
        return self::$tracer;
    }

}