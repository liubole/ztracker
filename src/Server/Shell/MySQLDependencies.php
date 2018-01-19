<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 9:36
 */
namespace Tricolor\ZTracker\Server\Shell;

use Tricolor\ZTracker\Server;

class MySQLDependencies
{
    public function run($day)
    {
        isset($day) OR ($day = date('Y_m-d'));
        $dependencies = new Server\Jobs\Dependencies();
        $dependencies->day($day)->run();
    }
}


