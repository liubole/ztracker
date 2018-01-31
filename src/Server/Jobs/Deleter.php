<?php
/**
 * User: Tricolor
 * Date: 2018/1/31
 * Time: 10:41
 */
namespace Tricolor\ZTracker\Server\Jobs;

use Tricolor\ZTracker\Storage;
use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Common;

class Deleter extends Job
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
     * @var Storage\Mysql\MysqlConnection
     */
    private static $conn;

    public function __construct()
    {
        parent::__construct();
        $this->timezone = ini_get('date.timezone');
        self::$conn = Storage\Mysql\MysqlConnection::getConnection(Config\Storage\Mysql::get());
    }

    /**
     * @param $day
     * @return Deleter
     */
    public function day($day)
    {
        $this->day = $day;
        $this->log('>Delete data for ' . $day . '...');
        $this->midnight = Common\Util::midnightUTC($day, $this->timezone);
        return $this;
    }

    /**
     * @param $defense
     */
    public function run($defense = true)
    {
        $this->checkup($defense);
        $spans = $this->getSpans();
        foreach ($spans as $span) {
            $this->deleteAnnotations($span);
            $this->deleteSpan($span);
        }
        $this->deleteDependencies();
        $this->catchSig();
    }

    /**
     * @return bool|mixed
     */
    private function deleteDependencies()
    {
        $d = new Storage\Mysql\Dependencies();
        $where = "`day`='" . $this->day . "'";
        return $d->delete($where, 1);
    }

    /**
     * @param $span
     * @return bool|mixed
     */
    private function deleteSpan($span)
    {
        if (isset($span['trace_id']) && isset($span['id']) && $span['trace_id'] && $span['id']) {
            $where = Common\Util::strFormat("`trace_id`='%s' AND `id`='%s'", $span['trace_id'], $span['id']);
            $s = new Storage\Mysql\Span();
            return $s->delete($where);
        }
        return false;
    }

    /**
     * @param $span
     * @return bool|mixed
     */
    private function deleteAnnotations($span)
    {
        if (isset($span['trace_id']) && isset($span['id']) && $span['trace_id'] && $span['id']) {
            $where = Common\Util::strFormat("`trace_id`='%s' AND `span_id`='%s'", $span['trace_id'], $span['id']);
            $a = new Storage\Mysql\Annotations();
            return $a->delete($where);
        }
        return false;
    }

    /**
     * @return \Generator
     */
    private function getSpans()
    {
        $traces = $this->getTraces();
        foreach ($traces as $trace) {
            $trace_id = $trace['trace_id'];
            $linksQuery = Common\Util::strFormat(
                "select `trace_id`,`id` from `zipkin_spans` where `trace_id`='%s'",
                $trace_id);
            $pdo = self::$conn->pureQuery($linksQuery);
            while($row = $pdo->fetch(\PDO::FETCH_ASSOC)) {
                yield $row;
            }
        }
    }

    private function getTraces()
    {
        $microsLower = bcmul($this->midnight, 1000000, 0);
        $microsUpper = bcsub(bcadd($microsLower, Common\Util::daysToMicros(), 0), 1, 0);
        $linksQuery = Common\Util::strFormat(
            "SELECT SQL_BIG_RESULT DISTINCT s.trace_id as trace_id FROM zipkin_spans s WHERE s.parent_id IS NULL AND (s.start_ts between %s AND %s)",
            $microsLower, $microsUpper);
        $pdo = self::$conn->pureQuery($linksQuery);
        while($trace = $pdo->fetch(\PDO::FETCH_ASSOC)) {
            yield $trace;
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
