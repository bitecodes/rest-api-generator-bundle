<?php

namespace Fludio\RestApiGeneratorBundle\Api\Routing\Action;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Routing\Route;

class ActionList implements \ArrayAccess, \Iterator
{
    /**
     * @var Action[]
     */
    protected $actions = [];
    /**
     * @var int
     */
    protected $position;

    public function __construct()
    {
        $this->position = 0;
    }

    public function add(Action $action)
    {
        $this->actions[get_class($action)] = $action;
    }

    /**
     * @return Action[]
     */
    public function all()
    {
        return $this->actions;
    }

    public function get($actionClass)
    {
        return $this->actions[$actionClass];
    }

    public function getNames()
    {
        $names = [];

        foreach ($this->actions as $action) {
            $refl = new \ReflectionClass($action);
            $names[] = Inflector::tableize($refl->getShortName());
        }

        return $names;
    }

    public function getActionFromRoute(Route $route)
    {

        foreach ($this->actions as $action) {
            $isRoute = $action->getUrlSchema() == $route->getPath()
                && $action->getMethods() == $route->getMethods();

            if ($isRoute) {
                return $action;
            }
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->actions[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->actions[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->actions[] = $value;
        } else {
            $this->actions[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->actions[$offset]);
    }

    public function current()
    {
        return $this->actions[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->actions[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }


}
