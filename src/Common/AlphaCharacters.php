<?php
/**
 * User: Tricolor
 * Date: 2018/1/18
 * Time: 19:13
 */
namespace Tricolor\ZTracker\Common;

class AlphaCharacters
{
    private $w = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_";
    private $needle;
    /**
     * @var AlphaCharacters
     */
    private $child;

    /**
     * @var AlphaCharacters
     */
    private $parent;

    public function __construct()
    {
        $this->needle = 0;
    }

    /**
     * @return string
     */
    public function chr()
    {
        return $this->snap();
    }

    /**
     * @return string
     * @param $moved
     */
    public function snap(&$moved = false)
    {
        $res = $this->w[$this->needle] . ($this->child ? $this->child->snap($moved) : '');
        if (!$moved) {
            $moved = $this->walk();
        }
        if (!$moved && !isset($this->parent)) {
            $this->newChild();
        }
        return $res;
    }

    /**
     * Create New Child
     */
    public function newChild()
    {
        if (!isset($this->child)) {
            $child = new AlphaCharacters();
            $this->child($child->parent($this));
            $this->resetAhead();
        } else {
            $this->child->newChild();
        }
    }

    /**
     * @return bool
     */
    public function walk()
    {
        if ($this->needle + 1 < strlen($this->w)) {
            $this->needle++;
            $this->resetBehind();
            return true;
        }
        return false;
    }

    /**
     * Reset needle ahead
     */
    public function resetAhead()
    {
        $this->needle = 0;
        if (isset($this->parent)) {
            $this->parent->resetAhead();
        }
    }

    /**
     * Reset needle behind
     * @param int $affect
     */
    public function resetBehind($affect = 0x1)
    {
        if (!$affect & 0x1) {
            $this->needle = 0;
        }
        $affect >>= 1;
        if (isset($this->child)) {
            $this->child->resetBehind($affect);
        }
    }

    /**
     * @param AlphaCharacters $parent
     * @return AlphaCharacters
     */
    public function parent(AlphaCharacters &$parent)
    {
        $this->parent = &$parent;
        return $this;
    }

    /**
     * @param AlphaCharacters $child
     * @return AlphaCharacters
     */
    public function child(AlphaCharacters &$child)
    {
        $this->child = &$child;
        return $this;
    }

}