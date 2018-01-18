<?php
/**
 * User: Tricolor
 * Date: 2018/1/17
 * Time: 13:42
 */
namespace Tricolor\ZTracker\Server;

class NodeLinks
{
    /**
     * @var array
     */
    private $traces = array();

    /**
     * @param $row
     */
    public function putTrace(&$row)
    {
        $trace_id = $row['trace_id'];
        $parent_id = $row['parent_id'];
        $id = $row['id'];
        $key = $row['a_key'];
        $service_name = $row['endpoint_service_name'];
        $type = $row['a_type'];
        $id_str = $this->nodeKey($trace_id, $id);

        // new trace
        if (!isset($this->traces[$trace_id])) {
            $this->traces[$trace_id] = array();
        }

        // new node
        $nodes =& $this->traces[$trace_id];
        if (!isset($nodes[$id_str])) {
            $nodes[$id_str] = new Node($trace_id, $parent_id, $id, $key, $service_name, $type);
        } else {
            // node exists
            $node =& $nodes[$id_str];
            if ($node instanceof Node) {
                $node->add($trace_id, $parent_id, $id, $key, $service_name, $type);
            }
        }
    }

    /**
     * @return array
     */
    public function getTraces()
    {
        return $this->traces;
    }

    /**
     * @param Node $node
     * @return null|Node
     */
    public function &findParent(Node &$node)
    {
        if ($node->isRoot()) return null;
        $id_str = $node->trace_id . $node->parent_id;
        $nodes =& $this->traces[$node->trace_id];
        return ($nodes && isset($nodes[$id_str])) ? $nodes[$id_str] : null;
    }

    /**
     * @param $trace_id
     * @param $id
     * @return string
     */
    private function nodeKey($trace_id, $id)
    {
        return $trace_id . '.' . $id;
    }
}