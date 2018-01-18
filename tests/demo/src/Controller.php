<?php
/**
 * User: Tricolor
 * Date: 2018/1/4
 * Time: 22:12
 */
namespace Tricolor\ZTracker\Demo;

use Tricolor\ZTracker\Core\GlobalTracer;

class Controller
{
    private static $instance;
    static $load = array();

    public function __construct()
    {
        self::$instance =& $this;
    }

    public static function &get_instance()
    {
        return self::$instance;
    }

    /**
     * @param $class
     * @param $as
     * @return mixed
     */
    public function load($class, $as)
    {
        $as = strtolower(str_replace('\\', '_', trim($as, '\\')));
        $obj =& self::get_instance();
        if (!isset(self::$load[$as])) {
            $obj->$as = new $class();
            self::$load[$as] = $as;
        }
        return $obj->$as;
    }

    public function output($output, $noencode = false)
    {
        $id = defined('CLIENTID') ? CLIENTID : '';

        $tracer = GlobalTracer::tracer();
        $tracer->log($id . 'Output', $output);
        $tracer->currentSpan()->end();
        $tracer->flush();
        
        echo json_encode($output);
        die();
    }
}