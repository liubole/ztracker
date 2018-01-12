<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 21:21
 */
namespace Tricolor\Tracker\Demo;

class Api extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function doTouch()
    {
        $return = array();
        foreach ($_POST as $k => $v) {
            $return[$k] = $v . Utils::randStr('alpha', rand(100, 1000));
        }
        // use 100 - 1000ms
        usleep(rand(100 * 1000, 1000 * 1000));
        $this->output($return);
    }
}