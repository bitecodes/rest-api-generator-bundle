<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Actions;

use Doctrine\Common\Inflector\Inflector;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
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

    /**
     * Action constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->action = $this->getActionName();
    }

    /**
     * @param array $params
     * @param int $referenceType
     * @return string
     */
    public function getUrl($params = [], $referenceType = Router::ABSOLUTE_PATH)
    {
        return $this->router->generate($this->getRouteName(), $params, $referenceType);
    }

    /**
     * @return string
     */
    public function getUrlSchema()
    {
        if ($this->urlType == self::URL_TYPE_COLLECTION) {
            return '/' . $this->apiResource->getConfigName();
        } elseif ($this->urlType == self::URL_TYPE_ELEMENT) {
            return '/' . $this->apiResource->getConfigName() . '/{' . $this->apiResource->getIdentifier() . '}';
        }
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param ApiResource $apiResource
     */
    public function setApiResource(ApiResource $apiResource)
    {
        $this->apiResource = $apiResource;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get the name of a route action
     *
     * @param ApiResource $parentResource
     * @return string
     */
    public function getRouteName(ApiResource $parentResource = null)
    {
        $parentResourceName = $parentResource ? '.' . $parentResource->getName() : '';

        return $this->apiResource->getBundlePrefix() . $parentResourceName . '.' . $this->apiResource->getName() . '.' . $this->action;
    }

    /**
     * @return string
     */
    public function getControllerAction()
    {
        return $this->apiResource->getControllerServiceName() . ':' . $this->action . 'Action';
    }

    /**
     * @return string
     */
    protected function getActionName()
    {
        $refl = new \ReflectionClass($this);
        return Inflector::tableize($refl->getShortName());
    }
}
