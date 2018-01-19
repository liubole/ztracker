<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 16:32
 */
namespace Tricolor\ZTracker\Server\Jobs;

use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Storage;
use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Exception;

class Trace extends Job
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $span_array
     */
    public function accept($span_array)
    {
        if (!$span_array) {
            return;
        }
        foreach ($span_array as $arr) {
            $span = Core\Span::revertFromArray($arr);
            $this->pretreatment($span);
            if ($span->shared) {
                if ($part_of_span = $this->getPartSpan($span)) {
                    $span->merge($part_of_span);
                    $this->saveSpan($span);
                } else {
                    $this->savePartSpan($span);
                }
            } else {
                $this->saveSpan($span);
            }
        }
        $this->catchSig();
    }

    /**
     * @param Core\Span $span
     */
    private function pretreatment(Core\Span &$span)
    {
        if ($span->remoteEndpoint) {
            if ($span->kind == Core\SpanKind\Client) {
                $span->addBinaryAnnotation(Core\BinaryAnnotation::create(
                    Core\Constants::SERVER_ADDR, true,
                    Core\BinaryAnnotationType::BOOL, $span->remoteEndpoint));
            } elseif ($span->kind == Core\SpanKind\Server) {
                $span->addBinaryAnnotation(Core\BinaryAnnotation::create(
                    Core\Constants::CLIENT_ADDR, true,
                    Core\BinaryAnnotationType::BOOL, $span->remoteEndpoint));
            } elseif ($span->kind == Core\SpanKind\Producer) {
                $span->addBinaryAnnotation(Core\BinaryAnnotation::create(
                    Core\Constants::MESSAGE_ADDR, true,
                    Core\BinaryAnnotationType::BOOL, $span->remoteEndpoint));
            }
        }
        if ($span->kind == Core\SpanKind\Client) {
            $span->addAnnotation(Core\Annotation::create(
                Core\Constants::CLIENT_RECV, $span->timestamp, $span->localEndpoint));
	        $span->addAnnotation(Core\Annotation::create(
                Core\Constants::CLIENT_SEND, Common\Util::endTs($span->timestamp, $span->duration), $span->localEndpoint));
        } elseif ($span->kind == Core\SpanKind\Server) {
            $span->addAnnotation(Core\Annotation::create(
                Core\Constants::SERVER_RECV, $span->timestamp, $span->localEndpoint));
            $span->addAnnotation(Core\Annotation::create(
                Core\Constants::SERVER_SEND, Common\Util::endTs($span->timestamp, $span->duration), $span->localEndpoint));
        } elseif ($span->kind == Core\SpanKind\Producer) {
            $span->addAnnotation(Core\Annotation::create(
                Core\Constants::MESSAGE_SEND, $span->timestamp, $span->localEndpoint));
        } elseif ($span->kind == Core\SpanKind\Consumer) {
            $span->addAnnotation(Core\Annotation::create(
                Core\Constants::MESSAGE_RECV, $span->timestamp, $span->localEndpoint));
        }
    }

    /**
     * @param Core\Span $span
     * @return mixed
     */
    private function saveSpan(Core\Span &$span)
    {
        // save annotations
        $this->saveAnnotations($span);

        // save binaryAnnotations
        $this->saveBinaryAnnotations($span);

        // save span
        $entity = new Storage\Mysql\Span();
        return $entity->enrich(array(
            'trace_id' => $span->traceId,
            'id' => $span->id,
            'name' => $span->name,
            'parent_id' => $span->parentId,
            'debug' => $span->debug,
            'start_ts' => $span->timestamp,
            'duration' => $span->duration
        ))->save();
    }

    /**
     * @param Core\Span $span
     */
    private function saveAnnotations(Core\Span &$span)
    {
        if (!$span->annotations) {return;}
        foreach ($span->annotations as $annotation) {
            $entity = new Storage\Mysql\Annotations();
            $endpoint = $annotation->endpoint ? $annotation->endpoint : $span->localEndpoint;
            $entity->enrich(array(
                'trace_id' => $span->traceId,
                'span_id' => $span->id,
                'a_key' => $annotation->value,
                'a_value' => null,
                'a_type' => -1,
                'a_timestamp' => $annotation->timestamp ? $annotation->timestamp : $span->timestamp,
                'endpoint_ipv4' => $endpoint ? ip2long($endpoint->ipv4) : null,
                'endpoint_ipv6' => $endpoint ? $endpoint->ipv6 : null,
                'endpoint_port' => $endpoint ? $endpoint->port : null,
                'endpoint_service_name' => $endpoint ? $endpoint->serviceName : null,
            ))->save();
        }
    }

    /**
     * @param Core\Span $span
     */
    private function saveBinaryAnnotations(Core\Span &$span)
    {
        if ($span->annotations) {
            foreach ($span->binaryAnnotations as $annotation) {
                $entity = new Storage\Mysql\Annotations();
                $endpoint = $annotation->endpoint ? $annotation->endpoint : $span->localEndpoint;
                $entity->enrich(array(
                    'trace_id' => $span->traceId,
                    'span_id' => $span->id,
                    'a_key' => $annotation->key,
                    'a_value' => $annotation->value,
                    'a_type' => Core\BinaryAnnotationType::guess($annotation),
                    'a_timestamp' => $span->timestamp,// null?
                    'endpoint_ipv4' => $endpoint ? ip2long($endpoint->ipv4) : null,
                    'endpoint_ipv6' => $endpoint ? $endpoint->ipv6 : null,
                    'endpoint_port' => $endpoint ? $endpoint->port : null,
                    'endpoint_service_name' => $endpoint ? $endpoint->serviceName : null,
                ))->save();
            }
        }
        // tags
        if ($span->tags) {
            foreach ($span->tags as $key => $val) {
                $entity = new Storage\Mysql\Annotations();
                $endpoint = $span->localEndpoint;
                $entity->enrich(array(
                    'trace_id' => $span->traceId,
                    'span_id' => $span->id,
                    'a_key' => $key,
                    'a_value' => $val,
                    'a_type' => Core\BinaryAnnotationType::guess($val),
                    'a_timestamp' => $span->timestamp,// null?
                    'endpoint_ipv4' => $endpoint ? ip2long($endpoint->ipv4) : null,
                    'endpoint_ipv6' => $endpoint ? $endpoint->ipv6 : null,
                    'endpoint_port' => $endpoint ? $endpoint->port : null,
                    'endpoint_service_name' => $endpoint ? $endpoint->serviceName : null,
                ))->save();
            }
        }
    }

    /**
     * @param Core\Span $span
     * @return null|Core\Span
     * @throws Exception\UnusableException
     */
    private function getPartSpan(Core\Span &$span)
    {
        $rs = $this->getRedis();
        if (!$rs) {
            throw new Exception\UnusableException('redis is unusable!');
        }
        $res = $rs->get($span->idString());
        $part_of_span = $res !== false ? $this->decode($res) : null;
        return $part_of_span;
    }

    /**
     * @param Core\Span $span
     * @return bool
     * @throws Exception\UnusableException
     */
    private function savePartSpan(Core\Span &$span)
    {
        $rs = $this->getRedis();
        if (!$rs) {
            throw new Exception\UnusableException('redis is unusable!');
        }
        $res = $rs->set($span->idString(), $this->encode($span), 1800);
        return $res;
    }

    /**
     * @param Core\Span $span
     * @return string
     */
    private function encode(Core\Span &$span)
    {
//        return json_encode($span->convertToArray());
        return @serialize($span->convertToArray());
    }

    /**
     * @param $str
     * @return Core\Span
     */
    public function decode($str)
    {
//        return Core\Span::revertFromArray(@json_decode($str, 1));
        return Core\Span::revertFromArray(@unserialize($str));
    }

    /**
     * @return \Redis
     */
    private function getRedis()
    {
        $try = 1;
        do {
            $rs = Storage\Redis\Connection::getConnection(Config\LiveStorage::getRedis());
            if (!$rs) usleep(rand(100, 500));
        } while (!$rs && ($try++ < 3));
        return $rs;
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}