<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Actions;

use Symfony\Component\Routing\Route;

/**
 * Class ActionList
 *
 * @package BiteCodes\RestApiGeneratorBundle\Api\Actions
 */
class ActionList implements \IteratorAggregate
{
    /**
     * @var Action[]
     */
    protected $actions = [];

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->actions);
    }

    /**
     * @param Action $action
     */
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

    /**
     * @param $actionClass
     * @return Action
     */
    public function get($actionClass)
    {
        return $this->actions[$actionClass];
    }

    /**
     * @param Route $route
     * @return Action
     */
    public function getActionForRoute(Route $route)
    {
        foreach ($this->actions as $action) {
            // TODO this should be improved to be more precise (route prefix)
            $sameSchema = strpos($route->getPath(), $action->getUrlSchema()) >= 0;
            $sameMethods = $action->getMethods() == $route->getMethods();

            if ($sameMethods && $sameSchema) {
                return $action;
            }
        }
    }
}
