<?php
/**
 * User: Tricolor
 * Date: 2018/1/16
 * Time: 20:52
 */
namespace Tricolor\ZTracker\Exception;

class UnusableException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}