<?php
/**
 * User: Tricolor
 * Date: 2018/1/13
 * Time: 21:47
 */
namespace Tricolor\ZTracker\Carrier;

Interface Base
{
    /**
     * @param $var
     * @return null|HttpHeaders|RabbitMQHeaders|Base
     */
    public function pipe(&$var);
}