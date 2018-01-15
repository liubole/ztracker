<?php
/**
 * User: Tricolor
 * Date: 2018/1/10
 * Time: 9:17
 */
namespace Tricolor\ZTracker\Server\Builder;

use Tricolor\ZTracker\Server\DependencyLink;

class DependencyLinkBuilder
{
    /**
     * String
     */
public  $parent;
    /**
     * String
     */
    public  $child;
    /**
     * long
     */
    public  $callCount;
    /**
     * long
     */
    public  $errorCount;

    public function __construct(DependencyLink $source = null)
    {
        if (!$source) {
            return;
        }
        $this->parent = $source->parent;
        $this->child = $source->child;
        $this->callCount = $source->callCount;
        $this->errorCount = $source->errorCount;
    }

    /**
     * @param $parent String
     * @return $this
     */
    public function parent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param $child String
     * @return mixed
     */
    public function child($child)
    {
        $this->child = $child;
        return $this;
    }

    /**
     * @param $callCount => Long
     * @return mixed
     */
    public function callCount($callCount)
    {
        $this->callCount = $callCount;
        return $this;
    }

    /**
     * @param $errorCount => Long
     * @return mixed
     */
    public function errorCount($errorCount)
    {
        $this->errorCount = $errorCount;
        return $this;
    }

    /**
     * @return DependencyLink
     */
    public function build()
    {
        return new DependencyLink($this);
    }
}