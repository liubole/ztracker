<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 9:25
 */
namespace Tricolor\ZTracker\Storage\Mysql;

use Tricolor\ZTracker\Config;
use Tricolor\ZTracker\Common;

class Model
{
    /**
     * @var MysqlConnection
     */
    private static $conn;

    public function __construct()
    {
        if (!self::$conn) {
            self::$conn = MysqlConnection::getConnection(Config\Storage\Mysql::get());
        }
    }

    /**
     * @param $vars
     * @return Model
     */
    public function enrich($vars)
    {
        foreach (array_intersect_key(get_object_vars($this), $vars) as $key => $val) {
            $this->$key = $vars[$key];
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function replaceInto()
    {
        self::log("REPLACE INTO " . self::getTable());
        $vars = get_object_vars($this);
        $keys = array_keys($vars);
        $values = array_values($vars);
        $sql = 'REPLACE INTO %s (`%s`) VALUES (\'%s\')';
        $query = Common\Util::strFormat($sql, self::getTable(), implode('`, `', $keys), implode("', '", $values));
        return $affected = self::$conn->pureQuery($query)->rowCount();
    }

    /**
     * @param $sets array
     * @param $where string
     * @param $limit int
     * @return int
     */
    public function update($sets, $where, $limit)
    {
        return $row_count = self::$conn
            ->update(self::getTable())
            ->cols($sets)
            ->where($where)
            ->limit($limit)
            ->query();
    }

    /**
     * @param $select string
     * @param $where string
     * @return array
     */
    public function getRow($select, $where)
    {
        return self::$conn
            ->select($select)
            ->from(self::getTable())
            ->where($where)
            ->limit(1)
            ->row();
    }

    public function getCount($where)
    {
        return self::$conn
            ->select('count(*)')
            ->from(self::getTable())
            ->where($where)
            ->limit(1)
            ->single();
    }

    /**
     * @param $select string
     * @param $where string
     * @param $limit int
     * @return mixed
     */
    public function getRows($select, $where, $limit)
    {
        return self::$conn
            ->select($select)
            ->from(self::getTable())
            ->where($where)
            ->limit($limit)
            ->query();
    }

    /**
     * @return mixed|$insert_id
     */
    public function save()
    {
        return $this->insert(get_object_vars($this));
    }

    /**
     * @param $cols
     * @return bool|mixed
     */
    public function insert($cols)
    {
        try {
            self::log("INSERT INTO " . self::getTable());
            $cols = isset($cols) ? $cols : get_object_vars($this);
            return $insert_id = self::$conn
                ->insert(self::getTable())
                ->cols($cols)
                ->query();
        } catch (\PDOException $e) {
            self::log($e->getMessage(), true);
        }
        return false;
    }

    /**
     * @return string
     */
    public static function getTable()
    {
        $ref = new static();
        return $ref::table;
    }

    /**
     * @param $msg
     * @param $error
     */
    private static function log($msg, $error = false)
    {
        $error
            ? Common\Debugger::error($msg)
            : Common\Debugger::warning($msg);
    }
}