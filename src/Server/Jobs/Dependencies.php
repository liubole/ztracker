<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 9:28
 */
namespace Tricolor\ZTracker\Server\Jobs;

use Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Server;
use Tricolor\ZTracker\Storage;
use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Common;

class Dependencies extends Job
{
    /**
     * @var string date(e.g. 2018-01-22)
     */
    private $day;
    /**
     * @var float midnight in seconds
     */
    private $midnight;
    /**
     * @var string
     */
    private $timezone;
    /**
     * @var array
     */
    private $dependencies;
    /**
     * @var Storage\Mysql\MysqlConnection
     */
    private static $conn;
    /**
     * @var Server\NodeLinks
     */
    private $links;

    public function __construct()
    {
        parent::__construct();
        $this->dependencies = array();
        $this->links = new Server\NodeLinks();

        $this->timezone = ini_get('date.timezone');
        self::$conn = Storage\Mysql\MysqlConnection::getConnection(Config\Storage\Mysql::get());
    }

    /**
     * @param $day
     * @return Dependencies
     */
    public function day($day)
    {
        $this->day = $day;
        $this->log('>Dependencies at ' . $day . ' are in calculation...');
        $this->midnight = Common\Util::midnightUTC($day, $this->timezone);
        return $this;
    }

    /**
     * @param $defense
     */
    public function run($defense = true)
    {
        $this->checkup($defense);
        $rows = $this->fetch();
        foreach ($rows as $row) {
            $this->links->push($row);
        }
        $this->log('>There are ' . $this->links->nodesCount() . ' nodes in total!');
        $this->calOutRelations();
        $this->store();
        $this->catchSig();
    }

    /**
     *
     */
    private function calOutRelations()
    {
        $traces = $this->links->getTraces();
        // for each trace
        foreach ($traces as $trace_id => $nodes) {
            // for each node
            foreach ($nodes as $node) {
                if (!$node instanceof Server\Node) continue;

                // $parentNode(client) -> $node(server)
                $parentNode =& $this->links->findParent($node);

                // root node
                if (!$parentNode) continue;

                $child = $node->getServiceName(Core\Constants::SERVER_RECV);
                if (!$child) {
                    $child = $parentNode->getServiceName(Core\Constants::SERVER_ADDR);
                }
                $parent = $parentNode->getServiceName(Core\Constants::CLIENT_SEND);
                if (!$parent) {
                    $parent = $node->getServiceName(Core\Constants::CLIENT_ADDR);
                }
                if (!$child || !$parent) {
                    continue;
                }
                $count = $this->manageRelations($parent, $child);
                $this->log('>Parent-Child: ' . $parent . '-' . $child . ':' . $count);
            }
        }
    }

    /**
     *
     */
    private function store()
    {
        foreach ($this->dependencies as $parentChild => $callCount) {
            $pair = explode('<:', $parentChild);
            $d = new Storage\Mysql\Dependencies();
            $d->day($this->day)
                ->parent($pair[0])
                ->child($pair[1])
                ->callCount($callCount)
                ->replaceInto();
        }
    }

    /**
     * @param $parent
     * @param $child
     * @return bool|int|mixed
     */
    private function manageRelations($parent, $child)
    {
        if (!$parent || !$child) {
            return false;
        }
        $key = $parent . "<:" . $child;
        if (!isset($this->dependencies[$key])) {
            return $this->dependencies[$key] = 1;
        }
        return ++$this->dependencies[$key];
    }

    /**
     * @return \Generator
     */
    private function fetch()
    {
        $microsLower = bcmul($this->midnight, 1000000, 0);
        $microsUpper = bcsub(bcadd($microsLower, Common\Util::daysToMicros(), 0), 1, 0);

        $fields = "s.trace_id, s.parent_id, s.id, a.a_key, a.endpoint_service_name, a.a_type";
        $groupByFields = str_replace("s.parent_id, ", "", $fields);
        $linksQuery = Common\Util::strFormat(
            "select SQL_BIG_RESULT distinct %s ".
                "from zipkin_spans s left outer join zipkin_annotations a on " .
                    "(s.trace_id = a.trace_id and s.id = a.span_id " .
                        "and a.a_key in ('ca', 'cs', 'sr', 'sa', 'error')) " .
                "where s.start_ts between %s and %s group by %s",
            $fields, $microsLower, $microsUpper, $groupByFields);
        $pdo = self::$conn->pureQuery($linksQuery);
        while($row = $pdo->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * @param $defense
     * @throws
     */
    private function checkup($defense)
    {
        if (!$defense) return;
        $time = strtotime($this->day);
        if ($time === false || $time === -1) {
            throw new \Exception("Wrong day: " . $this->day);
        }
        if (date("Y-m-d", $time) === false) {
            throw new \Exception("Wrong day: " . $this->day);
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
