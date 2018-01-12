<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 9:37
 */
namespace Tricolor\ZTracker\Exception;

class IllegalArgumentException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}