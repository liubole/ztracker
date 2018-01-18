<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 18:18
 */
namespace Tricolor\ZTracker\Config;

class TraceEnv
{
    const TRACE_OFF = 512;
    const TRACE_ON = 768;

    const LOG_OFF = 2048;
    const LOG_ON = 3072;

    const REPORT_OFF = 8192;
    const REPORT_ON = 12288;

    const COMPRESS_ON = 32768;
    const COMPRESS_OFF = 49152;

    public static $timezone = 'Asia/Shanghai';
}