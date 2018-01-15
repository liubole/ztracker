<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 18:06
 */
namespace Tricolor\ZTracker\Server\Enum;

class BinaryAnnotationType extends \SplEnum
{
    const BOOL = 0;
    /** No encoding, or type is unknown. */
    const BYTES = 1;
    const I16 = 2;
    const I32 = 3;
    const I64 = 4;
    const DOUBLE = 5;
    /** The only type zipkin v1 supports search against. */
    const STRING = 6;

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns {@link Type#BYTES} if unknown.
     * @param $value
     * @return int
     */
    public function fromValue($value)
    {
        switch ($value) {
            case 0:
                return BinaryAnnotationType::BOOL;
            case 1:
                return BinaryAnnotationType::BYTES;
            case 2:
                return BinaryAnnotationType::I16;
            case 3:
                return BinaryAnnotationType::I32;
            case 4:
                return BinaryAnnotationType::I64;
            case 5:
                return BinaryAnnotationType::DOUBLE;
            case 6:
                return BinaryAnnotationType::STRING;
            default:
                return BinaryAnnotationType::BYTES;
        }
    }
}