<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 18:18
 */
namespace Tricolor\ZTracker\Config;

class TraceEnv
{
    const SAMPLED = 0xc0;
    const NOT_SAMPLED = 0x128;

    const TRACE_OFF = 0x200;
    const TRACE_ON = 0x300;

    const LOG_OFF = 0x800;
    const LOG_ON = 0xc00;

    const REPORT_OFF = 0x2000;
    const REPORT_ON = 0x3000;

//    const COMPRESS_ON = 0x8000;
//    const COMPRESS_OFF = 0xc000;

    public static $timezone = 'Asia/Shanghai';

    public static $sampleRate = 5;
}