<?php

namespace Fludio\RestApiGeneratorBundle\Api\Routing\Action;

use Doctrine\Common\Inflector\Inflector;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Fludio\RestApiGeneratorBundle\Api\Response\ApiProblem;
use Symfony\Component\Routing\Router;

abstract class Action
{
    const URL_TYPE_ELEMENT = 'element';
    const URL_TYPE_COLLECTION = 'collection';
    /**
     * @var array
     */
    protected $methods;
    /**
     * @var string
     */
    protected $urlType;
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var ApiResource
     */
    protected $apiResource;
    /**
     * @var string
     */
    protected $action;
    /**
     * @var array
     */
    protected $roles;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->action = $this->getActionName();
    }

    public function getUrl($params = [], $referenceType = Router::ABSOLUTE_PATH)
    {
        return $this->router->generate($this->getRouteName(), $params, $referenceType);
    }

    public function getUrlSchema()
    {
        if ($this->urlType == self::URL_TYPE_COLLECTION) {
            return '/' . $this->apiResource->getName();
        } elseif ($this->urlType == self::URL_TYPE_ELEMENT) {
            return '/' . $this->apiResource->getName() . '/{id}';
        }
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function setApiResource(ApiResource $apiResource)
    {
        $this->apiResource = $apiResource;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get the name of a route action
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->apiResource->getBundlePrefix() . '.' . $this->apiResource->getName() . '.' . $this->action;
    }

    public function getControllerAction()
    {
        return $this->apiResource->getControllerServiceName() . ':' . $this->action . 'Action';
    }

    protected function getActionName()
    {
        $refl = new \ReflectionClass($this);
        return Inflector::tableize($refl->getShortName());
    }
}
