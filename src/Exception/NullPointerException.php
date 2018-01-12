<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 16:47
 */
namespace Tricolor\ZTracker\Exception;

class NullPointerException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}