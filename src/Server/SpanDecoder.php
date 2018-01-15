<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:17
 */
namespace Tricolor\ZTracker\Server;

use Tricolor\ZTracker\Server\Decoder;

Interface SpanDecoder
{
    const JSON_DECODER = Decoder\JsonCodec;

    /**
     * throws {@linkplain IllegalArgumentException} if a span couldn't be decoded
     * @param $span => byte[]
     * @return mixed => Span
     */
    public function readSpan($span);

    /**
     * throws {@linkplain IllegalArgumentException} if the spans couldn't be decoded
     * @param $span => byte[]
     * @return mixed => List<Span>
     */
    public function readSpans($span);
}