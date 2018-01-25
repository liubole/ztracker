<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 18:43
 */
namespace Tricolor\ZTracker\Server\Jobs;

use Tricolor\ZTracker\Prepare;
use Tricolor\ZTracker\Common;

class Job
{
    protected static $_OS = 'linux';
    /**
     * Handler constructor.
     * @param array $signals
     */
    public function __construct($signals = null)
    {
        self::checkSapiEnv();
        self::daemonize();
        echo get_class($this) . " start! pid:" . $this->getPid()  . PHP_EOL;
        if (extension_loaded('pcntl')) {
            $signals = isset($signals) ? $signals : array(SIGTERM);
            $that = &$this;
            foreach ($signals as $signal) {
                $this->regSig($signal, function () use (&$that) {
                    $that->signal(SIGTERM);
                });
            }
        }
        Prepare\SetEnv::timezone();
    }

    /**
     * @param $signal
     */
    public function signal($signal)
    {
        switch ($signal) {
            case SIGTERM:
                echo "Signal: $signal, ready to exit!" . PHP_EOL;
                exit(0);
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
        if (function_exists('pcntl_signal_dispatch')) {
            return pcntl_signal_dispatch();
        }
        return false;
    }

    /**
     * @param $msg
     */
    public function log($msg)
    {
        Common\Debugger::info($msg);
    }

    /**
     * run in daemon
     * @throws \Exception
     */
    protected static function daemonize()
    {
        if (static::$_OS !== 'linux') {
            return;
        }
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new \Exception('fork fail');
        } else if ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            throw new \Exception("setsid fail");
        }
    }

    /**
     *
     */
    protected static function checkSapiEnv()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            self::$_OS = 'windows';
        }
    }

    public function getPid()
    {
        return getmypid();
    }

    /**
     *
     */
    public function __destruct()
    {
        echo get_class($this) . " end! pid:" . $this->getPid() . PHP_EOL;
    }
}