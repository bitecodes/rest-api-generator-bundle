<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Resource;

class ApiManager
{
    /**
     * @var ApiResource[]
     */
    protected $resources = [];

    /**
     * @param ApiResource $config
     */
    public function addResource(ApiResource $config)
    {
        $this->resources[$config->getEntityClass()] = $config;
        $config->setManager($this);
    }

    /**
     * @param ApiResource[] $configs
     */
    public function setResources(array $configs)
    {
        foreach ($configs as $config) {
            $this->addResource($config);
        }
    }

    /**
     * @return ApiResource[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param $entityClass
     * @return bool|ApiResource
     */
    public function getResourceForEntity($entityClass)
    {
        if (!isset($this->resources[$entityClass])) {
            return false;
        }

        return $this->resources[$entityClass];
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return 'bite_codes.rest_api_generator';
    }
}