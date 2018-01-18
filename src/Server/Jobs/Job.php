<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 18:43
 */
namespace Tricolor\ZTracker\Server\Jobs;

class Job
{
    /**
     * Handler constructor.
     * @param array $signals
     */
    public function __construct($signals = array(SIGTERM))
    {
        echo get_class($this) . " start!" . PHP_EOL;

        $that = &$this;
        foreach ($signals as $signal) {
            $this->regSig($signal, function () use (&$that) {
                $that->signal(SIGTERM);
            });
        }
    }

    /**
     * @param $signal
     */
    public function signal($signal)
    {
        switch ($signal) {
            case SIGTERM:
                echo "Signal: $signal, ready to exit!" . PHP_EOL;
                exit;
                break;
        }
    }

    /**
     * @param $signal
     * @param $callable
     * @param null $_params
     * @return bool true|false
     */
    public function regSig($signal, $callable, $_params = null)
    {
        $args = func_get_args();
        return pcntl_signal($signal, function ($signal) use (&$callable, &$args) {
            if (count($args) > 2) {
                return call_user_func_array(
                    $callable, array_merge(array($signal), array_slice($args, 2)));
            }
            return call_user_func($callable, $signal);
        }, false);
    }

    /**
     * @return bool true|false
     */
    public function catchSig()
    {
        return pcntl_signal_dispatch();
    }

    /**
     *
     */
    public function __destruct()
    {
        echo get_class($this) . " end!" . PHP_EOL;
    }
}