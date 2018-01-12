<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 13:33
 */
namespace Tricolor\ZTracker\Core;

class Context
{
    public function __construct($vars = null)
    {
        if ($vars) {
            foreach ($vars as $key => $val) {
                $this->set($key, $val);
            }
        }
    }

    public function set($key, $value)
    {
        $this->$key = $value;
        return $this;
    }

    public function del($key)
    {
        if ($key) {
            unset($this->$key);
        }
    }

    public function get($key)
    {
        return $key ? $this->$key : null;
    }

    /**
     * @param $vars
     * @return Context
     */
    public static function create($vars)
    {
        return new Context($vars);
    }

}