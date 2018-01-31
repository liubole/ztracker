<?php
/**
 * User: Tricolor
 * Date: 2018/1/31
 * Time: 10:38
 */
namespace Tricolor\ZTracker\Server\Shell;

use Tricolor\ZTracker\Server;

/**
 * Usage:
 * 1.php delete.php `date -u -d '2 month ago' +%F`
 * 2.delete.php:
 *   $day = count($argv) >= 2 ? date("Y-m-d", strtotime($argv[1])) : date('Y-m-d');
 *   $deleter = new Server\Shell\MysqlDeleter();
 *   $deleter->run($day);
 * Class MysqlDeleter
 * @package Tricolor\ZTracker\Server\Shell
 */
class MysqlDeleter
{
    public function run($day)
    {
        isset($day) OR ($day = date('Y-m-d'));
        $dependencies = new Server\Jobs\Deleter();
        $dependencies->day($day)->run();
    }
}