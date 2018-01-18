<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 21:22
 */
namespace Tricolor\ZTracker\Demo;

class Client extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function touch()
    {
        /**
         * @var $rpc Tricolor\ZTracker\Demo\Rpc
         */
        $rpc = $this->load('Tricolor\ZTracker\Demo\Rpc', 'rpc');
        $res = $rpc->exec('touch', array(
            'time' => time(),
            'randStr' => Utils::randStr('alpha', rand(1000, 20000))
        ));
        $this->output($res);
    }
}

