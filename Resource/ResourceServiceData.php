<?php

namespace Fludio\RestApiGeneratorBundle\Resource;

class ResourceServiceData
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * ResourceService constructor.
     * @param array $options
     */
    public function __construct(array $options, Resource $resource)
    {
        $this->resource = $resource;
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
     * @return string
     */
    public function getFilterServiceName()
    {
        return $this->getServiceName('filter');
    }

    /**
     * Return a service name by conventions
     *
     * @param $service
     * @return string
     */
    protected function getServiceName($service)
    {
        return $this->resource->getBundlePrefix() . '.' . $service . '.' . $this->resource->getName();
    }
}