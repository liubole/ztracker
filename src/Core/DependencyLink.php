<?php
/**
 * User: Tricolor
 * Date: 2018/1/9
 * Time: 17:16
 */
namespace Tricolor\ZTracker\Core;

use Tricolor\ZTracker\Common\Util;
use Tricolor\ZTracker\Core\Builder\DependencyLinkBuilder;

class DependencyLink
{
    /** parent service name (caller) */
    public $parent;

    /** child service name (callee) */
    public $child;

    /** total traced calls made from {@link #parent} to {@link #child} */
    public $callCount;

    /** How many {@link #callCount calls} are known to be {@link Constants#ERROR errors} */
    public $errorCount;

    public function __construct(DependencyLinkBuilder $builder)
    {
        $this->parent = strtolower(Util::checkNotNull($builder->parent, "parent"));
        $this->child = strtolower(Util::checkNotNull($builder->child, "child"));
        $this->callCount = $builder->callCount;
        $this->errorCount = $builder->errorCount;
    }

    /**
     * @deprecated please use {@link #builder()}
     * @param $parent => String
     * @param $child => String
     * @param $callCount => long
     * @return mixed
     */
    public static function create($parent, $child, $callCount)
    {
        return (self::builder())->parent($parent)->child($child)->callCount($callCount)->build();
    }

    /**
     * @return DependencyLinkBuilder
     */
    public function toBuilder()
    {
        return new DependencyLinkBuilder($this);
    }

    /**
     * @return DependencyLinkBuilder
     */
    public static function builder()
    {
        return new DependencyLinkBuilder();
    }

    /**
     * @return string
     */
    public function hashCode()
    {
        return spl_object_hash($this);
    }
}