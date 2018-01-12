<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 18:18
 */
namespace Tricolor\ZTracker\Config;

class TraceEnv
{
    /**
     * 19-20 :report<2bit>
     * 21-22 :log<2bit>
     * 23-24 :trace<2bit>
     * 25    :no use
     * 26-32 :sample rate<5bit>
     */
    const TRACE_OFF = 512;
    const TRACE_ON = 768;

    const LOG_OFF = 2048;
    const LOG_ON = 3072;

    const REPORT_OFF = 8192;
    const REPORT_ON = 12288;

    const COMPRESS_ON = 32768;
    const COMPRESS_OFF = 49152;
}