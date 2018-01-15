<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 18:04
 */
namespace Tricolor\ZTracker\Server\Builder;

use Tricolor\ZTracker\Server\BinaryAnnotation;
use Tricolor\ZTracker\Server\Endpoint;

class BinaryAnnotationBuilder
{
    /**
     * @var String
     */
    private $key;
    /**
     * byte[]
     */
    private $value;
    /**
     * @var BinaryAnnotationType
     */
    private $type;
    /**
     * @var Endpoint
     */
    private $endpoint;

    public function __construct(BinaryAnnotation $source = null)
    {
        if (!$source) {
            return;
        }
        $this->key = $source->key;
        $this->value = $source->value;
        $this->type = $source->type;
        $this->endpoint = $source->endpoint;
    }

    /**
     * @see BinaryAnnotation#key
     * @param $key
     * @return $this
     */
    public function key($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @see BinaryAnnotation#value
     * @param $value string byte[]
     * @return $this
     */
    public function value($value)
    {
//        $this->value = $value;//getBytes
//        $this->type = BinaryAnnotationType::STRING;
        $this->value = $value;
        return $this;
    }

    /**
     * @see BinaryAnnotation#type
     * @param BinaryAnnotationType $type
     * @return $this
     */
    public function type(BinaryAnnotationType $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @see BinaryAnnotation#endpoint
     * @param Endpoint $endpoint
     * @return $this
     */
    public function endpoint(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @return BinaryAnnotation
     */
    public function build()
    {
        return new BinaryAnnotation($this->key, $this->value, $this->type, $this->endpoint);
    }
}