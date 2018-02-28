<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 18:06
 */
namespace Tricolor\ZTracker\Core;

class BinaryAnnotationType
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
     * @param $binaryAnnotation mixed|BinaryAnnotation
     * @return int|mixed
     */
    public static function guess(&$binaryAnnotation)
    {
        if ($binaryAnnotation instanceof BinaryAnnotation) {
            if (isset($binaryAnnotation->type)) {
                return $binaryAnnotation->type;
            }
            $val = $binaryAnnotation->value;
        } else {
            $val = &$binaryAnnotation;
        }

        if (is_bool($val)) {
            return self::BOOL;
        }
        if (is_int($val)) {
            return self::I32;
        }
        if (is_double($val)) {
            return self::DOUBLE;
        }
        if (is_string($val)) {
            return self::STRING;
        }
        return -1;
    }

    public static function softGuess(&$binaryAnnotation)
    {
        if ($binaryAnnotation instanceof BinaryAnnotation) {
            if (isset($binaryAnnotation->type)) {
                return $binaryAnnotation->type;
            }
            $val = $binaryAnnotation->value;
        } else {
            $val = &$binaryAnnotation;
        }
        $mayBeTypes = array();
        if (is_bool($val) || in_array(strtolower($val), array('true', 'false'), true)) {
            $mayBeTypes[] = self::BOOL;
        }
        if (is_int($val) || is_numeric($val)) {
            $mayBeTypes[] = self::I32;
        }
        if (is_double($val) || is_numeric($val)) {
            $mayBeTypes[] = self::DOUBLE;
        }
        if (is_string($val)) {
            $mayBeTypes[] = self::STRING;
        }
        return $mayBeTypes ? array_unique($mayBeTypes) : array(-1);
    }

}