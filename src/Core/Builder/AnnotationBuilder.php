<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:34
 */
namespace Tricolor\ZTracker\Core\Builder;

use Tricolor\ZTracker\Core\Annotation;
use Tricolor\ZTracker\Core\Endpoint;

class AnnotationBuilder
{
    private $timestamp;
    private $value;
    private $endpoint;

    public function __construct(Annotation $source = null)
    {
        if (!$source) {
            return;
        }
        $this->timestamp = $source->timestamp;
        $this->value = $source->value;
        $this->endpoint = $source->endpoint;
    }

    /**
     * @see Annotation#timestamp
     * @param $timestamp
     * @return $this
     */
    public function timestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @see Annotation#value
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @see Annotation#endpoint
     * @param Endpoint $endpoint
     * @return $this
     */
    public function endpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return Annotation
     */
    public function build()
    {
        return Annotation::create($this->timestamp, $this->value, $this->endpoint);
    }
}