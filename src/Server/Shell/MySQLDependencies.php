<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 9:36
 */
namespace Tricolor\ZTracker\Server\Shell;

use Tricolor\ZTracker\Server;

/**
 * Usage:
 * 1.php dependencies.php `date -u -d '1 day ago' +%F`
 * 2.dependencies.php:
 *  $day = count($argv) >= 2 ? date("Y-m-d", strtotime($argv[1])) : date('Y-m-d');
 *  $collector = new Server\Shell\MySQLDependencies();
 *  $collector->run($day);
 * Class MySQLDependencies
 * @package Tricolor\ZTracker\Server\Shell
 */
class MySQLDependencies
{
    public function run($day)
    {
        isset($day) OR ($day = date('Y-m-d'));
        $dependencies = new Server\Jobs\Dependencies();
        $dependencies->day($day)->run();
    }
}


