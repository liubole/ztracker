<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 18:23
 */
namespace Tricolor\ZTracker\Server;

use Tricolor\ZTracker\Common;

class QueryRequestBuilder
{
    private $serviceName;//String
    private $spanName;//String
    private $annotations = array();//List<String> 
    private $binaryAnnotations = array();//Map<String, String> 
    private $minDuration;//Long
    private $maxDuration;//Long
    private $endTs;//Long
    private $lookback;//Long
    private $limit;//Integer

    public function __construct(QueryRequest $source = null)
    {
        $this->serviceName = $source->serviceName;
        $this->spanName = $source->spanName;
        $this->annotations = $source->annotations;
        $this->binaryAnnotations = $source->binaryAnnotations;
        $this->minDuration = $source->minDuration;
        $this->maxDuration = $source->maxDuration;
        $this->endTs = $source->endTs;
        $this->lookback = $source->lookback;
        $this->limit = $source->limit;
    }

    /**
     * @see QueryRequest#serviceName
     * @param $serviceName
     * @return $this
     */
    public function serviceName($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * This ignores the reserved span name "all".
     *
     * @see QueryRequest#spanName
     * @param $spanName
     * @return $this
     */
    public function spanName($spanName)
    {
        $this->spanName = "all" == trim($spanName) ? null : $spanName;
        return $this;
    }

    /**
     * Corresponds to query parameter "annotationQuery". Ex. "http.method=GET and error"
     *
     * @see QueryRequest#toAnnotationQuery()
     * @param $annotationQuery
     * @return $this
     */
    public function parseAnnotationQuery($annotationQuery)
    {
        if ($annotationQuery != null && !empty($annotationQuery)) {
            foreach (explode(" and ", urldecode($annotationQuery)) as $ann) {
                $idx = strpos($ann, '=');
                if ($idx === false) {
                    $this->addAnnotation($ann);
                } else {
                    $keyValue = explode('=', $ann);
                    $this->addBinaryAnnotation(
                        substr($ann, 0, $idx),
                        count($keyValue) < 2 ? "" : substr($ann, $idx + 1)
                    );
                }
            }
        }
        return $this;
    }

    /**
     * @see QueryRequest#annotations
     *
     * @param $annotation String
     * @return $this
     */
    public function addAnnotation($annotation)
    {
        array_push($this->annotations, $annotation);
        return $this;
    }

    /**
     * @see QueryRequest#binaryAnnotations
     *
     * @param $key String
     * @param $value String
     * @return $this
     */
    public function addBinaryAnnotation($key, $value)
    {
        $this->binaryAnnotations[$key] = $value;
        return $this;
    }

    /**
     * @see QueryRequest#minDuration
     *
     * @param $minDuration > Long
     * @return $this
     */
    public function minDuration($minDuration)
    {
        $this->minDuration = $minDuration;
        return $this;
    }

    /**
     * @see QueryRequest#maxDuration
     * @param $maxDuration >Long
     * @return $this
     */
    public function maxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;
        return $this;
    }

    /**
     * @see QueryRequest#endTs
     *
     * @param $endTs >Long
     * @return $this
     */
    public function endTs($endTs)
    {
        $this->endTs = $endTs;
        return $this;
    }

    /**
     * @see QueryRequest#lookback
     *
     * @param $lookback >Long
     * @return $this
     */
    public function lookback($lookback)
    {
        $this->lookback = $lookback;
        return $this;
    }

    /**
     * @see QueryRequest#limit
     *
     * @param $limit int
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return QueryRequest
     */
    public function build()
    {
        $selectedEndTs = $this->endTs == null
            ? Common\Util::currentTimeMillis()
            : $this->endTs;
        return new QueryRequest(
            $this->serviceName,
            $this->spanName,
            $this->annotations,
            $this->binaryAnnotations,
            $this->minDuration,
            $this->maxDuration,
            $selectedEndTs,
            min($this->lookback == null ? $selectedEndTs : $this->lookback, $selectedEndTs),
            $this->limit == null ? 10 : $this->limit);
    }
}