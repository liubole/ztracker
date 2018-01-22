<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:25
 */
namespace Tricolor\ZTracker\Storage\Mysql;

class Span extends Model
{
    /**
     * bigint(20)
     */
    public $trace_id;

    /**
     * bigint(20)
     */
    public $id;

    /**
     * varchar(255)
     */
    public $name;

    /**
     * bigint(20)
     */
    public $parent_id;

    /**
     * bit(1)
     */
    public $debug;

    /**
     * bigint(20)
     */
    public $start_ts;

    /**
     * bigint(20)
     */
    public $duration;

    /**
     * db table
     */
    const table = "zipkin_spans";

    /**
     * @return bool|mixed
     */
    public function save()
    {
        $cols = get_object_vars($this);
        if (is_null($this->parent_id)) {
            unset($cols['parent_id']);
        }
        return $this->insert($cols);
    }

    public function __construct()
    {
        parent::__construct();
    }
}