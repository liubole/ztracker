<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 17:33
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Config;

class Decision
{
    private $decision;

    public function __construct($value = null)
    {
        if (isset($value)) {
            $this->decision = $value;
        }
    }

    public function getValue()
    {
        if (isset($this->decision)) {
            return $this->decision;
        }
        return null;
    }

    public function turn($on_off)
    {
        if (!in_array($on_off, $this->getAllSwitch(), true)
        ) {
            return false;
        }
        $move = floor(log($on_off, 2)) - 1;
        if ($move < 0) {
            return false;
        }
        $this->decision = $this->decision & (3 << $move) ^ $this->decision | $on_off;
        return $this;
    }

    public function sampled()
    {
        if (!isset($this->decision)) {
            return false;
        }
        if (($this->decision & Config\TraceEnv::SAMPLED) === Config\TraceEnv::SAMPLED) {
            return true;
        }
        if (($this->decision & Config\TraceEnv::NOT_SAMPLED) === Config\TraceEnv::NOT_SAMPLED) {
            return false;
        }
        return false;
    }

//    public function traceOn()
//    {
//        if (!isset($this->decision)) {
//            return true;
//        }
//        if (($this->decision & Config\TraceEnv::TRACE_ON) === Config\TraceEnv::TRACE_ON) {
//            return true;
//        }
//        if (($this->decision & Config\TraceEnv::TRACE_OFF) === Config\TraceEnv::TRACE_OFF) {
//            return false;
//        }
//        return true;
//    }

    public function reportOn()
    {
        if (!isset($this->decision)) {
            return true;
        }
        if (($this->decision & Config\TraceEnv::REPORT_ON) === Config\TraceEnv::REPORT_ON) {
            return true;
        }
        if (($this->decision & Config\TraceEnv::REPORT_OFF) === Config\TraceEnv::REPORT_OFF) {
            return false;
        }
        return true;
    }

    public function logOn()
    {
        if (!isset($this->decision)) {
            return true;
        }
        if (($this->decision & Config\TraceEnv::LOG_ON) === Config\TraceEnv::LOG_ON) {
            return true;
        }
        if (($this->decision & Config\TraceEnv::LOG_OFF) === Config\TraceEnv::LOG_OFF) {
            return false;
        }
        return true;
    }

    /**
     * @param $val
     * @return Decision
     */
    public static function revertFromInt($val)
    {
        return new Decision($val);
    }

    /**
     * @return null|int
     */
    public function convertToInt()
    {
        return (int)$this->decision;
    }

    private function getAllSwitch()
    {
        return array(
            Config\TraceEnv::SAMPLED,
            Config\TraceEnv::NOT_SAMPLED,
//            Config\TraceEnv::TRACE_OFF,
//            Config\TraceEnv::TRACE_ON,
            Config\TraceEnv::LOG_OFF,
            Config\TraceEnv::LOG_ON,
            Config\TraceEnv::REPORT_OFF,
            Config\TraceEnv::REPORT_ON
        );
    }
}