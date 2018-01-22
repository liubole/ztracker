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
            ->localEndpoint($this->localEndpoint())
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
        $sampled = Common\Util::sampleOrNot(Config\TraceEnv::$sampleRate)
            ? Config\TraceEnv::SAMPLED
            : Config\TraceEnv::NOT_SAMPLED;
        $decision->switchOver($sampled);
        $span = GlobalTracer::spanBuilder()
            ->traceId(Common\Util::traceId())
            ->id(Common\Util::spanId())
            ->name(Common\Util::getServerApi())
            ->decision($decision)
            ->localEndpoint($this->localEndpoint());
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
     * @return $this
     */
    public function extract()
    {
        $this->carrier->pipe($_SERVER)->extract();
        $content = $this->carrier->getContext();
        $span = $this->carrier->getSpan();

        $span->localEndpoint($this->localEndpoint());
        $this->currentContext($content);
        $this->currentSpan($span);
        $this->joinSpan($span);
        return $this;
    }

    public function flush()
    {
        if (!$this->reportSpans) {
            return;
        }
        $reports = array();
        foreach ($this->reportSpans as &$span) {
            if ($span instanceof Span) {
                if (!$span->traceId || !$span->id) continue;
                array_push($reports, $span->convertToArray());
            }
        }
        $reportOn = $this->currentSpan()->decision
            ? $this->currentSpan()->decision->reportOn()
            : true;
        $logOn = $this->currentSpan()->decision
            ? $this->currentSpan()->decision->logOn()
            : true;
        $logs = $spans = null;
        if ($reportOn && $reports) {
            $spans =& $reports;
        }
        if ($logOn && $this->logs) {
            $traceId = $spanId = $timestamp = null;
            if ($this->currentSpan()) {
                $traceId = $this->currentSpan()->traceId;
                $spanId = $this->currentSpan()->id;
                $timestamp = $this->currentSpan()->timestamp;
            }
            $h = Common\Util::currentInHuman($timestamp);
            $associate = $traceId
                ? array('traceId' => $traceId, 'spanId' => $spanId, 'timestamp' => $h,)
                : array('timestamp' => $h);
            foreach (array_keys($associate) as $key) {
                if (isset($this->logs[$key])) {
                    $this->logs[$key . '_' . Common\Util::random(6)] = $this->logs[$key];
                    unset($this->logs[$key]);
                }
            }
            $logs = array_merge($associate, $this->logs);
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