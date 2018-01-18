<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 9:49
 */
namespace Tricolor\ZTracker\Storage\Mysql;

class Dependencies extends Model
{
    /**
     * date
     */
    public $day;

    /**
     * varchar(255)
     */
    public $parent;

    /**
     * varchar(255)
     */
    public $child;

    /**
     * bigint(20)
     */
    public $call_count;

    /**
     * db table
     */
    const table = "zipkin_dependencies";

    /**
     * @param $parent
     * @return Dependencies
     */
    public function parent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param $child
     * @return Dependencies
     */
    public function child($child)
    {
        $this->child = $child;
        return $this;
    }

    /**
     * @param $call_count
     * @return Dependencies
     */
    public function callCount($call_count)
    {
        $this->call_count = $call_count;
        return $this;
    }

    public function __construct()
    {
        parent::__construct();
    }
}