<?php
/**
 * User: Tricolor
 * Date: 2018/1/31
 * Time: 10:38
 */
namespace Tricolor\ZTracker\Server\Shell;

use Tricolor\ZTracker\Server;

class Deleter
{
    public function run($day)
    {
        isset($day) OR ($day = date('Y-m-d'));
        $dependencies = new Server\Jobs\Deleter();
        $dependencies->day($day)->run();
    }
}