<?php

namespace Fludio\RestApiGeneratorBundle\Resource;

use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;

class ResourceManager
{
    /**
     * @var Resource[]
     */
    protected $endpoints = [];

    /**
     * @param ApiResource $config
     */
    public function addConfiguration(ApiResource $config)
    {
        $this->endpoints[$config->getEntityNamespace()] = $config;
        $config->setManager($this);
    }


    /**
     * @param ApiResource[] $configs
     */
    public function setConfigurations(array $configs)
    {
        foreach ($configs as $config) {
            $this->addConfiguration($config);
            $config->setManager($this);
        }
    }

    /**
     * @return ApiResource[]
     */
    public function getConfigurations()
    {
        return $this->endpoints;
    }

    /**
     * @param $entityClass
     * @return bool|ApiResource
     */
    public function getConfigurationForEntity($entityClass)
    {
        if (!isset($this->endpoints[$entityClass])) {
            return false;
        }

        return $this->endpoints[$entityClass];
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return 'fludio.rest_api_generator';
    }
}