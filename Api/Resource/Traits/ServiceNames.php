<?php

namespace Fludio\RestApiGeneratorBundle\Api\Resource\Traits;

trait ServiceNames
{
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
        return $this->getBundlePrefix() . '.' . $service . '.' . $this->getName();
    }
}