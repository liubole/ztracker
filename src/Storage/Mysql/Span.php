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

    public function __construct()
    {
        parent::__construct();
    }
}