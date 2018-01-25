<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 16:37
 */
namespace Tricolor\ZTracker\Server\QueryApi;

use Tricolor\ZTracker\Storage;
use Tricolor\ZTracker\Common;
use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Core;
use Tricolor\ZTracker\Server;
use Tricolor\ZTracker\Prepare;

class MysqlApi
{
    public $defaultLookBack = 86400000; // 1 day in millis
    private $conn;

    public function __construct()
    {
        $this->conn = Storage\Mysql\MysqlConnection::getConnection(Config\Storage\Mysql::get());
        Prepare\SetEnv::precision();
    }

    /**
     * api: /services
     */
    public function services()
    {
        $select = "zipkin_annotations.endpoint_service_name";
        $sql = "SELECT DISTINCT %s FROM zipkin_annotations WHERE (%s IS NOT NULL) AND (%s<>'')";
        $query = Common\Util::strFormat($sql, $select, $select, $select);
        $services = $this->conn->query($query, null, \PDO::FETCH_COLUMN);
        return $services ? $services : array();
    }

    /**
     * api: /spans/{serviceName}
     * @param $serviceName
     * @return array|mixed
     */
    public function spans($serviceName)
    {
        if (!$serviceName) return array();
        $serviceName = strtolower($serviceName);
        $tb_a = "zipkin_annotations";
        $tb_s = "zipkin_spans";
        $sql = "SELECT DISTINCT %s.name FROM zipkin_spans JOIN zipkin_annotations ON %s.trace_id = %s.trace_id AND %s.id=%s.span_id WHERE %s.endpoint_service_name='%s' ORDER BY %s.name";
        $query = Common\Util::strFormat($sql, $tb_s, $tb_s, $tb_a, $tb_s, $tb_a, $tb_a, $serviceName, $tb_s);
        $serviceNames = $this->conn->query($query, null, \PDO::FETCH_COLUMN);
        return $serviceNames ? $serviceNames : array();
    }

    /**
     * api: /traces
     *
     * 1> e.g. annotationQuery=http.api%3Dupload%2Fpic&endTs=1515326131460&limit=10&lookback=90031460&minDuration=1&serviceName=user%2Fadd&sortOrder=duration-desc&spanName=0
     * 2> sortOrder:
     * service-percentage-desc: 最大百分比优先
     * service-percentage-asc: 最小百分比优先
     * duration-desc: 耗时最长优先
     * duration-asc: 耗时最短优先
     * timestamp-desc: 时间倒序
     * timestamp-asc: 时间顺序
     *
     * @param Server\QueryRequest $request
     * @param $traceId
     * @return mixed
     */
    public function traces(Server\QueryRequest $request, $traceId = null)
    {
        if (is_null($request) && is_null($traceId)) {
            return array();
        }
        $allSpans = array();
        try {
            $spanFields = "`trace_id`,`id`,`name`,`parent_id`,`debug`,`start_ts`,`duration`";
            $traceIdCondition = $request != null
                ? $this->spanTraceIdCondition($this->toTraceIdQuery($request))
                : $this->spanTraceIdConditionSingle($traceId);
            $sql = "SELECT $spanFields FROM `zipkin_spans` WHERE $traceIdCondition";
            Common\Debugger::notice($sql);
            $res = $this->conn->query($sql);
            $spansWithoutAnnotations = $trace_ids = array();
            foreach ($res as $row) {
                $trace_id = $row['trace_id'];
                if (!isset($trace_ids[$trace_id])) {
                    $trace_ids[$trace_id] = 1;
                    $spansWithoutAnnotations[$trace_id] = array();
                }
                $spansWithoutAnnotations[$trace_id][] = array(
                    'traceId' => $this->idString($row['trace_id']),
                    'name' => $row['name'],
                    'id' => $this->idString($row['id']),
                    'parentId' => $this->idString($row['parent_id']),
                    'timestamp' => $this->microSec($row['start_ts']),
                    'duration' => $this->microSec($row['duration']),
                    'debug' => $row['debug'],
                );
            }
            $trace_ids = array_keys($trace_ids);

            $annotationFields = "trace_id,span_id,a_key,a_value,a_type,a_timestamp,endpoint_ipv4,endpoint_ipv6,endpoint_port,endpoint_service_name";
            $sql = "SELECT $annotationFields" .
                " FROM zipkin_annotations" .
                " WHERE zipkin_annotations.trace_id IN ('" . implode("','", $trace_ids) . "')" .
                " ORDER BY zipkin_annotations.a_timestamp ASC, zipkin_annotations.a_key ASC";
            $res = $this->conn->query($sql);
            $dbAnnotations = array();
            foreach ($res as $row) {
                $key = $row['trace_id'] . "." . $row['span_id'];
                $dbAnnotations[$key][] = $row;
            }

            foreach ($spansWithoutAnnotations as $trace_id => &$spans) {
                foreach ($spans as &$span) {
                    $key = $trace_id . "." . $span['id'];
                    if (!isset($dbAnnotations[$key])) continue;
                    foreach ($dbAnnotations[$key] as $a) {
                        $endpoint = $this->endpoint($a);
                        if ($a['a_type'] == -1) {
                            $span['annotations'][] = array(
                                'timestamp' => $this->microSec($a['a_timestamp']),
                                'value' => $a['a_key'],
                                'endpoint' => $endpoint,
                            );
                        } else {
                            $span['binaryAnnotations'][] = array(
                                'key' => $a['a_key'],
                                'value' => $a['a_value'],
                                'type' => $a['a_type'],
                                'endpoint' => $endpoint,
                            );
                        }
                    }
                    $allSpans[] = $span;
                }
            }
        } catch (\Exception $e) {
        }
        return Common\GroupByTraceId::apply($allSpans);
    }

    /**
     * api: /trace/{trace_id}
     * @param $traceId
     * @return array|mixed
     */
    public function trace($traceId)
    {
        $res = $this->traces(null, $traceId);
        return $res ? current($res) : array();
    }

    /**
     * api: /dependencies
     * @param $endTs
     * @param $lookback
     * @return array|mixed
     */
    public function dependencies($endTs, $lookback)
    {
        $days = Common\Util::getDays($endTs, $lookback);
        $fields = "zipkin_dependencies.day as date, zipkin_dependencies.parent as parent, zipkin_dependencies.child as child, zipkin_dependencies.call_count as callCount";
        $where = "zipkin_dependencies.day in ('" . implode("','", $days) . "')";
        $sql = "SELECT %s FROM zipkin_dependencies WHERE %s";
        $query = Common\Util::strFormat($sql, $fields, $where);
        $dependencies = $this->conn->query($query);
        return $dependencies ? $dependencies : array();
    }

    /**
     * @param $serviceName
     * @param $spanName
     * @param $annotationQuery
     * @param $minDuration
     * @param $maxDuration
     * @param $endTs
     * @param $lookback
     * @param $limit
     * @return Server\QueryRequest
     */
    public function newRequest($serviceName, $spanName, $annotationQuery, $minDuration, $maxDuration, $endTs, $lookback, $limit)
    {
        return Server\QueryRequest::builder()
            ->serviceName($serviceName)
            ->spanName($spanName)
            ->parseAnnotationQuery($annotationQuery)
            ->minDuration($minDuration)
            ->maxDuration($maxDuration)
            ->endTs($endTs)// in micro seconds
            ->lookback($lookback != null ? $lookback : $this->defaultLookBack)// in micro seconds
            ->limit($limit)
            ->build();
    }

    /**
     * @param $traceIdQuery
     * @return string
     */
    private function spanTraceIdCondition($traceIdQuery)
    {
        $traceIds = $this->conn->query($traceIdQuery, null, \PDO::FETCH_COLUMN);
        return "zipkin_spans.trace_id in ('" . implode("','", $traceIds) . "')";
    }

    /**
     * @param $traceId
     * @return string
     */
    private function spanTraceIdConditionSingle($traceId)
    {
        return "zipkin_spans.trace_id='" . $traceId . "'";
    }

    /**
     * @param Server\QueryRequest $request
     * @return string
     */
    private function toTraceIdQuery(Server\QueryRequest $request)
    {
        $endTs = ($request->endTs > 0 && $request->endTs != PHP_INT_MAX)
            ? bcmul($request->endTs, 1000, 0)
            : Common\Util::currentTimeMillis() * 1000;

        $table = "`zipkin_spans` JOIN `zipkin_annotations` ON " . $this->joinCondition("zipkin_annotations");

        $i = 0;
        foreach ($request->annotations as $key) {
            $aT = "a" . $i++;
            $aTable = "`zipkin_annotations` AS `$aT`";
            $table = $table . " JOIN $aTable ON " . $this->joinCondition($aT) .
                " AND `$aT`.`a_key`='$key'";
            $table = $this->maybeOnService($table, $aT, $request->serviceName);
        }

        foreach ($request->binaryAnnotations as $key => $val) {
            $aT = "a" . $i++;
            $aTable = "`zipkin_annotations` AS `$aT`";
            $table = $table . " JOIN $aTable ON " . $this->joinCondition($aT) .
                " AND `$aT`.`a_type`=" . Core\BinaryAnnotationType::STRING .
                " AND `$aT`.`a_key`='$key'" .
                " AND `$aT`.`a_value`='$val'";
            $table = $this->maybeOnService($table, $aT, $request->serviceName);
        }

        //List<SelectField<>
        $distinctFields = array('zipkin_spans.trace_id AS trace_id');
        $distinctFields[] = "MAX(zipkin_spans.start_ts) AS max_start_ts";

        $startTs = bcsub($endTs, bcmul($request->lookback, 1000, 0), 0);
        $dsl = "SELECT DISTINCT " . implode(',', $distinctFields) .
            " FROM " . $table .
            " WHERE (zipkin_spans.start_ts BETWEEN $startTs AND $endTs)";

        if ($request->serviceName != null) {
            $dsl .= " AND (zipkin_annotations.endpoint_service_name='" . $request->serviceName . "')";
        }

        if ($request->spanName != null) {
            $dsl .= " AND (zipkin_spans.name='" . $request->spanName . "')";
        }

        if ($request->minDuration != null && $request->maxDuration != null) {
            $dsl .= " AND (zipkin_spans.duration BETWEEN " . $request->minDuration . " AND " . $request->maxDuration . ")";
        } else if ($request->minDuration != null) {
            $dsl .= " AND (zipkin_spans.duration >= " . $request->minDuration . ")";
        }
        // see: $distinctFields
        $dsl .= " GROUP BY trace_id ORDER BY max_start_ts DESC LIMIT " . $request->limit;

        return $dsl;
    }

    /**
     * @param $table
     * @param $aTable
     * @param $serviceName
     * @return string
     */
    private function maybeOnService($table, $aTable, $serviceName)
    {
        if ($serviceName == null) return $table;
        return $table . " AND `$aTable`.`endpoint_service_name`='$serviceName'";
    }

    /**
     * @param $annotationTable
     * @return string
     */
    private function joinCondition($annotationTable)
    {
        return "`zipkin_spans`.`trace_id`=`$annotationTable`.`trace_id` AND `zipkin_spans`.`id`=`$annotationTable`.`span_id`";
    }

    /**
     * @param $a array one row data in table zipkin_annotations
     * @return array
     */
    private function endpoint($a)
    {
        $serviceName = $a['endpoint_service_name'];
        if ($serviceName == null) return null;
        return array(
            'serviceName' => $serviceName,
            'ipv4' => is_long($a['endpoint_ipv4']) ? long2ip($a['endpoint_ipv4']) : $a['endpoint_ipv4'],
            'ipv6' => $a['endpoint_ipv6'],
            'port' => $a['endpoint_port'],
        );
    }

    /**
     * @param $micro_sec
     * @return string
     */
    private function microSec($micro_sec)
    {
        return $micro_sec;
    }

    private function idString($id)
    {
        return (string)$id;
    }
}