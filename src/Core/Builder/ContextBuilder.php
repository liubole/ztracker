<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 13:35
 */
namespace Tricolor\ZTracker\Core\Builder;

use Tricolor\ZTracker\Core\Context;
use Tricolor\ZTracker\Core\Decision;
use Tricolor\ZTracker\Core\Span;

class ContextBuilder
{
    private $vars;

    public function __construct(Context $context = null)
    {
        if (!$context) {return;}
        $this->vars = get_object_vars($context);
    }

    /**
     * @param $array array
     * @return ContextBuilder
     */
    public function fromArray($array)
    {
        $this->vars = (array)$array;
        return $this;
    }

    /**
     * @return Context
     */
    public function build()
    {
        return Context::create($this->vars);
    }
}