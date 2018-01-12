<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 11:37
 */
namespace Tricolor\ZTracker\Core\Enum;

class SpanKind extends \SplEnum
{
    const CLIENT = '0';
    const SERVER = '1';
    const PRODUCER = '2';
    const CONSUMER = '3';
    const UNKNOWN = '4';

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
                return SpanKind::CLIENT;
            case 1:
                return SpanKind::SERVER;
            case 2:
                return SpanKind::PRODUCER;
            case 3:
                return SpanKind::CONSUMER;
            default:
                return SpanKind::UNKNOWN;
        }
    }
}