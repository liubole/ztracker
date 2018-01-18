<?php
/**
 * User: Tricolor
 * Date: 2018/1/15
 * Time: 18:57
 */
namespace Tricolor\ZTracker\Storage\Mysql;

class Annotations extends Model
{
    /**
     * bigint(20)
     */
    public $trace_id;

    /**
     * bigint(20)
     */
    public $span_id;

    /**
     * varchar(255)
     */
    public $a_key;

    /**
     * blob
     */
    public $a_value;

    /**
     * int(11)
     */
    public $a_type;

    /**
     * bigint(20)
     */
    public $a_timestamp;

    /**
     * int(11)
     */
    public $endpoint_ipv4;

    /**
     * binary(16)
     */
    public $endpoint_ipv6;

    /**
     * smallint(6)
     */
    public $endpoint_port;

    /**
     * varchar(255)
     */
    public $endpoint_service_name;

    /**
     * db table
     */
    const table = "zipkin_annotations";

    public function __construct()
    {
        parent::__construct();
    }
}