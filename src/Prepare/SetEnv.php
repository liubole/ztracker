<?php
/**
 * User: Tricolor
 * Date: 2018/1/24
 * Time: 11:39
 */
namespace Tricolor\ZTracker\Prepare;

use Tricolor\ZTracker\Config;

class SetEnv
{
    /**
     * @param bool $force
     */
    public static function timezone($force = true)
    {
        $timezone = $force
            ? Config\TraceEnv::$timezone
            : ini_get('date.timezone')
                ? ini_get('date.timezone')
                : Config\TraceEnv::$timezone;
        ini_set('date.timezone', $timezone);
    }

    /**
     * @param bool $force
     */
    public static function precision($force = true)
    {
        $precision = $force
            ? Config\TraceEnv::$precision
            : ini_get('precision')
                ? ini_get('precision')
                : Config\TraceEnv::$precision;
        ini_set('precision', $precision);
    }
}