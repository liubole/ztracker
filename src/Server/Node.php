<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 13:47
 */
namespace Tricolor\ZTracker\Server;

class Node
{
    public $trace_id;
    public $parent_id;
    public $id;
    public $annotations;

    public function __construct($trace_id, $parent_id, $id, $key, $service_name, $type)
    {
        $this->trace_id = $trace_id;
        $this->parent_id = $parent_id;
        $this->id = $id;
        $this->annotations = array();
        $this->addKey($key, $service_name, $type);
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return !$this->parent_id;
    }

    /**
     * @param $trace_id
     * @param $parent_id
     * @param $id
     * @param $key
     * @param $service_name
     * @param $type
     */
    public function add($trace_id, $parent_id, $id, $key, $service_name, $type)
    {
        if ($this->trace_id == $trace_id && $this->parent_id == $parent_id && $this->id == $id) {
            $this->addKey($key, $service_name, $type);
        }
    }

    /**
     * @param $key
     * @return null
     */
    public function getServiceName($key)
    {
        if (!$this->annotations || !isset($this->annotations[$key])) {
            return null;
        }
        return $this->annotations[$key]['service_name'];
    }

    /**
     * @param $key
     * @param $service_name
     * @param $type
     */
    private function addKey($key, $service_name, $type)
    {
        if (!isset($this->annotations[$key])) {
            $this->annotations[$key] = array(
                'key' => $key,
                'service_name' => $service_name,
                'type' => $type,
            );
        }
    }
}