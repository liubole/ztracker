<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 9:36
 */
namespace Tricolor\ZTracker\Server\Shell;
include_once __DIR__ . "/../../../vendor/autoload.php";

use Tricolor\ZTracker\Server;

// php MySQLDependenciesCront.php `date -u -d '1 day ago' +%F`
$day = count($argv) >= 2 ? date("Y-m-d", strtotime($argv[1])) : date('Y_m-d');
$dependencies = new Server\Jobs\Dependencies();
$dependencies->day($day)->run();