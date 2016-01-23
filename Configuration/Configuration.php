<?php

namespace Fludio\ApiAdminBundle\Configuration;

use Doctrine\Common\Inflector\Inflector;

class Configuration
{
    const ROUTE_INDEX = 'index';
    const ROUTE_SHOW = 'show';
    const ROUTE_CREATE = 'create';
    const ROUTE_UPDATE = 'update';
    const ROUTE_DELETE = 'delete';
    const ROUTE_BATCH_DELETE = 'batch_delete';
    const ROUTE_BATCH_UPDATE = 'batch_update';

    private $entity;
    /**
     * @var Convention
     */
    private $convention;

    public function __construct($entity, Convention $convention)
    {
        $this->entity = $entity;
        $this->convention = $convention;
    }

    /**
     * Return the entity
     *
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entity;
    }

    /**
     * Return the url for a given route
     *
     * @param $route
     * @return mixed
     * @throws \Exception
     */
    public function getUrl($route)
    {
        switch ($route) {
            case self::ROUTE_INDEX:
            case self::ROUTE_CREATE:
            case self::ROUTE_BATCH_UPDATE:
            case self::ROUTE_BATCH_DELETE:
                $url = $this->getCollectionUrl();
                break;
            case self::ROUTE_SHOW:
            case self::ROUTE_UPDATE:
            case self::ROUTE_DELETE:
                $url = $this->getSingleUrl();
                break;
            default:
                throw new \Exception('Invalid route');
                break;
        }

        return $url;
    }

    /**
     * Return the base url for this entity
     *
     * @return string
     */
    public function getResourceBaseUrl()
    {
        return $this->getCollectionUrl();
    }

    public function getResourceName()
    {
        return $this->convention->getResourceName($this->entity);
    }

    /**
     * Return string of the controller service method for an action
     *
     * @param $action
     * @return string
     */
    public function getControllerAction($action)
    {
        return $this->getControllerServiceName() . ':' . $action . 'Action';
    }

    /**
     * Get the name of a route action
     *
     * @param $action
     * @return string
     */
    public function getRouteName($action)
    {
        return $this->convention->getBundlePrefix() . '.' . $action . '.' . $this->convention->getResourceName($this->entity);
    }

    /**
     * Get service name of controller
     *
     * @return string
     */
    public function getControllerServiceName()
    {
        return $this->getServiceName('controller');
    }

    /**
     * Get service name of entity handler
     *
     * @return string
     */
    public function getEntityHandlerServiceName()
    {
        return $this->getServiceName('entity_handler');
    }

    /**
     * Get service name of form handler
     *
     * @return string
     */
    public function getFormHandlerServiceName()
    {
        return $this->getServiceName('form_handler');
    }


    /**
     * Get service name of entity handler
     *
     * @return string
     */
    public function getRepositoryServiceName()
    {
        return $this->getServiceName('repositories');
    }

    /**
     * Return collection url for an entity
     *
     * @return string
     */
    protected function getCollectionUrl()
    {
        $resourceName = $this->convention->getResourceName($this->entity);
        return '/' . Inflector::pluralize(strtolower($resourceName));
    }

    /**
     * Return single item url for an entity
     *
     * @return string
     */
    protected function getSingleUrl()
    {
        return $this->getCollectionUrl() . '/{id}';
    }

    /**
     * Return a service name by conventions
     *
     * @param $service
     * @return string
     */
    protected function getServiceName($service)
    {
        return $this->convention->getBundlePrefix() . '.' . $service . '.' . $this->convention->getResourceName($this->entity);
    }
}