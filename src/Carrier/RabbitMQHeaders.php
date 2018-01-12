<?php
/**
 * User: Tricolor
 * Date: 2017/12/20
 * Time: 9:33
 */
namespace Tricolor\ZTracker\Carrier;
use PhpAmqpLib\Wire\AMQPTable;
use Tricolor\Tracker\Common\StrUtils;
use Tricolor\Tracker\Core\Context;

class RabbitMQHeaders implements Base
{
    private $prefix = 'Tr-';
    private $msgObj;

    /**
     * RabbitMQHeaders constructor.
     * @param $msgObj \PhpAmqpLib\Message\AMQPMessage
     */
    public function __construct()
    {
        $this->msgObj = &$msgObj;
    }

    /**
     * @return bool
     */
    public function unpack()
    {
        if (!is_object($this->msgObj)) return false;
        try {
            $hdr = $this->msgObj->get('application_headers');
            $headers = $hdr->getNativeData();
            if (!$headers) {
                return false;
            }
        } catch (\Exception $e) {
            Logger::log(Debug::INFO, __METHOD__ . ': unpack exception : ' . $e->getMessage());
            return false;
        }
        $trace = array();
        foreach ($headers as $key => $val) {
            if (StrUtils::startsWith($key, $this->prefix)) {
                $trace[substr($key, strlen($this->prefix))] = $val;
            }
        }
        if ($trace) {
            Context::set($trace);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function pack()
    {
        if (!is_object($this->msgObj)) return false;
        try {
            try {
                $hdr = $this->msgObj->get('application_headers');
            } catch (\Exception $e) {
                $hdr = new AMQPTable();
            }
            foreach (Context::toArray() as $k => $v) {
                $hdr->set($this->prefix . $k, $v, AMQPTable::T_STRING_LONG);
            }
            $this->msgObj->set('application_headers', $hdr);
            return true;
        } catch (\Exception $e) {
            Logger::log(Debug::INFO, __METHOD__ . ': pack exception : ' . $e->getMessage());
        }
        return false;
    }
}