<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 14:05
 */
namespace Tricolor\ZTracker\Server;

class Trace
{
    private $spans = array();

    private function __construct()
    {
    }

    /**
     * @return Trace
     */
    public static function newTrace()
    {
        return new self();
    }

    public function addSpan($span)
    {
        array_push($this->spans, $span);
    }
}