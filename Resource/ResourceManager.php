<?php

namespace Fludio\ApiAdminBundle\Resource;

class ResourceManager
{
    /**
     * @var Resource[]
     */
    protected $endpoints = [];

    /**
     * @param Resource $config
     */
    public function addConfiguration(Resource $config)
    {
        $this->endpoints[$config->getEntityNamespace()] = $config;
        $config->setManager($this);
    }


    /**
     * @param Resource[] $configs
     */
    public function setConfigurations(array $configs)
    {
        foreach ($configs as $config) {
            $this->addConfiguration($config);
            $config->setManager($this);
        }
    }

    /**
     * @return Resource[]
     */
    public function getConfigurations()
    {
        return $this->endpoints;
    }

    /**
     * @param $entityClass
     * @return bool|Resource
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
        return 'fludio.api_admin';
    }
}