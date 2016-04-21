<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Resource;

class ApiManager
{
    /**
     * @var ApiResource[]
     */
    protected $resources = [];

    /**
     * @param ApiResource $apiResource
     */
    public function addResource(ApiResource $apiResource)
    {
        $this->resources[$apiResource->getName()] = $apiResource;
        $apiResource->setManager($this);
    }

    /**
     * @param ApiResource[] $apiResources
     */
    public function setResources(array $apiResources)
    {
        foreach ($apiResources as $apiResource) {
            $this->addResource($apiResource);
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
     * @param $name
     * @return ApiResource|bool
     */
    public function getResource($name)
    {
        if (!isset($this->resources[$name])) {
            return false;
        }

        return $this->resources[$name];
    }

    /**
     * @param $entityClass
     * @return bool|ApiResource
     */
    public function getResourceForEntity($entityClass)
    {
        $resources = array_filter($this->getResources(), function (ApiResource $resource) use ($entityClass) {
            return $resource->getEntityClass() === $entityClass;
        });

        if (empty($resources)) {
            return false;
        }

        return array_pop($resources);
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return 'bite_codes.rest_api_generator';
    }
}