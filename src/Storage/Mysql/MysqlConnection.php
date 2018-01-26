<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:44
 */
namespace Tricolor\ZTracker\Storage\Mysql;

use Tricolor\ZTracker\Common;
use \Workerman\MySQL\Connection;

class MysqlConnection extends Connection
{
    private static $conns = array();

    public function __construct($host, $port, $user, $password, $db_name, $charset = 'utf8')
    {
        parent::__construct($host, $port, $user, $password, $db_name, $charset);
    }

    /**
     * @param string $query
     * @param null $params
     * @return \PDOStatement
     */
    public function pureQuery($query = '', $params = null)
    {
        $query = trim($query);
        if (empty($query)) {
            $query = $this->build();
            if (!$params) {
                $params = $this->getBindValues();
            }
        }

        $this->resetAll();
        $this->lastSql = $query;
        $this->execute($query, $params);
        return $this->sQuery;
    }

    public function replace($table)
    {
        $this->type = 'REPLACE';
        $this->table = $this->quoteName($table);
        return $this;
    }

    public function batchInsert($table, $fields, $batch_cols)
    {
        $insert_values = array($table);
        $question_marks = array();
        foreach ($batch_cols as $d) {
            $question_marks[] = '(' . $this->placeholders('?', sizeof($d)) . ')';
            $insert_values = array_merge($insert_values, array_values($d));
        }
        $sql = "INSERT INTO ? (" . implode(",", $fields) . ") VALUES " . implode(',', $question_marks);

        try {
            $this->beginTrans();
            $this->execute($sql, $insert_values);
            $this->commitTrans();
        } catch (\PDOException $e) {
            $this->rollBackTrans();
        }
    }

    private function placeholders($text, $count=0, $separator=",")
    {
        $result = array();
        if ($count > 0) {
            for ($x = 0; $x < $count; $x++) {
                $result[] = $text;
            }
        }
        return implode($separator, $result);
    }

    /**
     * @param $config
     * @return MysqlConnection
     * @throws \Exception
     */
    public static function getConnection($config)
    {
        try {
            $host = Common\Util::checkNotNull($config['host'], 'database config.host is null!');
            $port = Common\Util::checkNotNull($config['port'], 'database config.port is null!');
            $username = Common\Util::checkNotNull($config['username'], 'database config.username is null!');
            $password = Common\Util::checkNotNull($config['password'], 'database config.password is null!');
            $database = Common\Util::checkNotNull($config['database'], 'database config.database is null!');
            if (!isset(self::$conns[$database])) {
                self::$conns[$database] = new MysqlConnection($host, $port, $username, $password, $database);
            }
        } catch (\Exception $e) {
            Common\Debugger::fatal($e->getMessage());
            throw $e;
        }
        return self::$conns[$database];
    }
}