<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 17:33
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Config\TraceEnv;

class Decision
{
    public $decision;

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

    public function setValue($value)
    {
        $this->decision = (int)$value;
        return $this;
    }

    public function sampleRate()
    {
        if (!isset($this->decision)) {return 0;}
        return $this->decision & 127;
    }

    public function traceOn()
    {
        if (!isset($this->decision)) {
            return true;
        }
        if (($this->decision & TraceEnv::TRACE_ON) === TraceEnv::TRACE_ON) {
            return true;
        }
        if (($this->decision & TraceEnv::TRACE_OFF) === TraceEnv::TRACE_OFF) {
            return false;
        }
        return true;
    }

    public function isReportOn()
    {
        if (!isset($this->decision)) {
            return true;
        }
        if (($this->decision & TraceEnv::REPORT_ON) === TraceEnv::REPORT_ON) {
            return true;
        }
        if (($this->decision & TraceEnv::REPORT_OFF) === TraceEnv::REPORT_OFF) {
            return false;
        }
        return true;
    }

    public function logOn()
    {
        if (!isset($this->decision)) {
            return true;
        }
        if (($this->decision & TraceEnv::LOG_ON) === TraceEnv::LOG_ON) {
            return true;
        }
        if (($this->decision & TraceEnv::LOG_OFF) === TraceEnv::LOG_OFF) {
            return false;
        }
        return true;
    }

    public function toArray()
    {
        return $this->decision;
    }
}