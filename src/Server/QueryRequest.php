<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 18:19
 */
namespace Tricolor\ZTracker\Server;

use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Core;

class QueryRequest
{
    /**
     * When present, corresponds to {@link zipkin.Endpoint#serviceName} and constrains all other
     * parameters.
     */
    public $serviceName;

    /** When present, only include traces with this {@link zipkin.Span#name} */
    public $spanName;

    /**
     * Include traces whose {@link zipkin.Span#annotations} include a value in this set, or where
     * {@link zipkin.Span#binaryAnnotations} include a String whose key is in this set.
     *
     * <p> This is an AND condition against the set, as well against {@link #binaryAnnotations}
     */
    public $annotations;

    /**
     * Include traces whose {@link zipkin.Span#binaryAnnotations} include a String whose key and
     * value are an entry in this set.
     *
     * <p> This is an AND condition against the set, as well against {@link #annotations}
     */
    public $binaryAnnotations;

    /**
     * Only return traces whose {@link zipkin.Span#duration} is greater than or equal to
     * minDuration microseconds.
     */
    public $minDuration;

    /**
     * Only return traces whose {@link zipkin.Span#duration} is less than or equal to maxDuration
     * microseconds. Only valid with {@link #minDuration}.
     */
    public $maxDuration;

    /**
     * Only return traces where all {@link zipkin.Span#timestamp} are at or before this time in
     * epoch milliseconds. Defaults to current time.
     */
    public $endTs;

    /**
     * Only return traces where all {@link zipkin.Span#timestamp} are at or after (endTs -
     * lookback) in milliseconds. Defaults to endTs.
     */
    public $lookback;

    /** Maximum number of traces to return. Defaults to 10 */
    public $limit;

    /**
     * Corresponds to query parameter "annotationQuery". Ex. "http.method=GET and error"
     *
     * @see QueryRequest.Builder#parseAnnotationQuery(String)
     */
    public function toAnnotationQuery()
    {
        $annotationQuery = "";
        $i = 0;
        $count = count($this->binaryAnnotations);
        foreach ($this->binaryAnnotations as $key => $val) {
            $annotationQuery .= $key . '=' . $val;
            if ((++$i < $count) || !empty($this->annotations)) {
                $annotationQuery .= " and ";
            }
        }
        $i = 0;
        foreach ($this->annotations as $val) {
            $annotationQuery .= $val;
            if (++$i < $count) {
                $annotationQuery .= " and ";
            }
        }

        return strlen($annotationQuery) > 0 ? $annotationQuery : null;
    }

    public function __construct($serviceName, $spanName, $annotations, $binaryAnnotations, $minDuration, $maxDuration, $endTs, $lookback, $limit)
    {
        Common\Util::checkArgument($serviceName == null || !$serviceName . isEmpty(), "serviceName was empty");
        Common\Util::checkArgument($spanName == null || !$spanName . isEmpty(), "spanName was empty");
        Common\Util::checkArgument($endTs > 0, "endTs should be positive, in epoch microseconds: was %d", $endTs);
        Common\Util::checkArgument($limit > 0, "limit should be positive: was %d", $limit);
        $this->serviceName = $serviceName != null ? strtolower($serviceName) : null;
        $this->spanName = $spanName != null ? strtolower($spanName) : null;
        $this->annotations = $annotations;
        foreach ($annotations as $annotation) {
            Common\Util::checkArgument(!empty($annotation), "annotation was empty");
            Common\Util::checkArgument(!in_array($annotation, Core\Constants::CORE_ANNOTATIONS),
                "queries cannot be refined by core annotations: %s", $annotation);
        }
        $this->binaryAnnotations = $binaryAnnotations;
        foreach ($binaryAnnotations as $key => $val) {
            Common\Util::checkArgument(!empty($key), "binary annotation key was empty");
            Common\Util::checkArgument(!empty($val), "binary annotation value for %s was empty", $key);
        }
        if ($minDuration != null) {
            Common\Util::checkArgument($minDuration > 0, "minDuration must be a positive number of microseconds");
            $this->minDuration = $minDuration;
            if ($maxDuration != null) {
                Common\Util::checkArgument($maxDuration >= $minDuration, "maxDuration should be >= minDuration");
                $this->maxDuration = $maxDuration;
            } else {
                $this->maxDuration = null;
            }
        } else {
            Common\Util::checkArgument($maxDuration == null, "maxDuration is only valid with minDuration");
            $this->minDuration = $this->maxDuration = null;
        }
        $this->endTs = $endTs;
        $this->lookback = $lookback;
        $this->limit = $limit;
    }

    public function toBuilder()
    {
        return new QueryRequestBuilder($this);
    }

    public static function builder()
    {
        return new QueryRequestBuilder();
    }

    public function toString()
    {
        return "QueryRequest{"
            . "serviceName=" . $this->serviceName . ", "
            . "spanName=" . $this->spanName . ", "
            . "annotations=" . $this->annotations . ", "
            . "binaryAnnotations=" . $this->binaryAnnotations . ", "
            . "minDuration=" . $this->minDuration . ", "
            . "maxDuration=" . $this->maxDuration . ", "
            . "endTs=" . $this->endTs . ", "
            . "lookback=" . $this->lookback . ", "
            . "limit=" . $this->limit
            . "}";
    }
    
    /**
     * @param $o
     * @return bool
     */
    public function equals($o)
    {
        if ($o instanceof QueryRequest) {
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
     * Tests the supplied trace against the current request
     * @param $spans
     * @return bool
     */
    public function test($spans)
    {
        $timestamp = $this->guessTimestamp($spans[0]);
        if ($timestamp == null ||
            $timestamp < ($this->endTs - $this->lookback) * 1000 ||
            $timestamp > $this->endTs * 1000
        ) {
            return false;
        }
        $serviceNames = array();
        $testedDuration = $this->minDuration == null && $this->maxDuration == null;

        $spanNameToMatch = $this->spanName;
        $annotationsToMatch = array_flip(array_unique($this->annotations));//Set<String>
        $binaryAnnotationsToMatch = $this->binaryAnnotations;//Map<String, String>

        foreach ($spans as $span) {
            $currentServiceNames = array();

            foreach ($span->annotations as $a) {
                if ($this->appliesToServiceName($a->endpoint, $this->serviceName)) {
                    unset($annotationsToMatch[$a->value]);
                }
                if ($a->endpoint != null) {
                    array_push($serviceNames, $a->endpoint->serviceName);
                    array_push($currentServiceNames, $a->endpoint->serviceName);
                }
            }

            foreach ($span->binaryAnnotations as $b) {
                if ($this->appliesToServiceName($b->endpoint, $this->serviceName) &&
                    $b->type == Core\BinaryAnnotationType::STRING &&
                    ($b->value == $binaryAnnotationsToMatch[$b->key])
                ) {
                    unset($binaryAnnotationsToMatch[$b->key]);
                }
                if ($this->appliesToServiceName($b->endpoint, $this->serviceName)) {
                    unset($annotationsToMatch[$b->key]);
                }
                if ($b->endpoint != null) {
                    array_push($serviceNames, $b->endpoint->serviceName);
                    array_push($currentServiceNames, $b->endpoint->serviceName);
                }
            }

            if (($this->serviceName == null || in_array($this->serviceName, $currentServiceNames)) && !$testedDuration) {
                if ($this->minDuration != null && $this->maxDuration != null) {
                    $testedDuration = $span->duration >= $this->minDuration && $span->duration <= $this->maxDuration;
                } else if ($this->minDuration != null) {
                    $testedDuration = $span->duration >= $this->minDuration;
                }
            }

            if ($span->name == $spanNameToMatch) {
                $spanNameToMatch = null;
            }
        }
        $serviceNames = array_unique($serviceNames);
        return ($serviceNames == null || in_array($this->serviceName, $serviceNames))
            && $spanNameToMatch == null
            && empty($annotationsToMatch)
            && empty($binaryAnnotationsToMatch)
            && $testedDuration;
    }

    private static function appliesToServiceName(Core\Endpoint $endpoint, $serviceName)
    {
        return $serviceName == null || $endpoint == null || $endpoint->serviceName == $serviceName;
    }

    private static function guessTimestamp(Core\Span $span)
    {
        if (!$span) {
            return null;
        }
        if ($span->timestamp != null || empty($span->annotations)) {
            return $span->timestamp;
        }
        $rootServerRecv = null;
        for ($i = 0, $length = count($span->annotations); $i < $length; $i++) {
            $annotation = $span->annotations[$i];
            if ($annotation instanceof Core\Annotation) {
                if ($annotation->value == Core\Constants::CLIENT_SEND) {
                    return $annotation->timestamp;
                } else if ($annotation->value == Core\Constants::SERVER_RECV) {
                    $rootServerRecv = $annotation->timestamp;
                }
            }
        }
        return $rootServerRecv;
    }
}