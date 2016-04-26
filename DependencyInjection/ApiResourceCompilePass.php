<?php

namespace BiteCodes\RestApiGeneratorBundle\DependencyInjection;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Doctrine\Common\Inflector\Inflector;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ApiResourceCompilePass implements CompilerPassInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    public static $allActions = [
        'index', 'show', 'create', 'update', 'delete', 'batch_delete', 'batch_update'
    ];

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        foreach ($this->getManager()->getResources() as $apiResource) {
            $this->addActions($apiResource);
//            $this->addSubResources($apiResource);
        }
    }

    /**
     * @param ApiResource $apiResource
     */
    protected function addActions(ApiResource $apiResource)
    {
        $options = $this->getOptionsForApiResource($apiResource);
        $def = $this->getDefinitionForApiResource($apiResource);


        foreach ($this->getActionsForApiResource($apiResource) as $actionName) {
            $class = Inflector::classify($actionName);
            $actionClass = "BiteCodes\\RestApiGeneratorBundle\\Api\\Actions\\$class";
            $action = new Definition($actionClass);
            $action->addArgument(new Reference('router'));
            $action->addMethodCall('setRoles', [$options['routes'][$actionName]['roles']]);
            $this->container->set('bite_codes.rest_api_generator.action.' . $apiResource->getName() . '.' . Inflector::tableize($actionName), $action);
            $def->addMethodCall('addAction', [$action]);
        }
    }

    /**
     * @param ApiResource $apiResource
     */
    protected function addSubResources(ApiResource $apiResource)
    {
        $options = $this->getOptionsForApiResource($apiResource);

        if (empty($options['sub_resources'])) {
            return;
        }

        foreach ($options['sub_resources'] as $subResourceName => $config) {
            $name = $apiResource->getName() . '_' . $subResourceName;
            $subResource = $this->getManager()->getResource($name);

            $apiResource->addSubResource($subResource);
        }
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->container->getParameter('bite_codes.rest_api_generator.config');
    }

    /**
     * @param ApiResource $apiResource
     * @return array
     */
    protected function getOptionsForApiResource(ApiResource $apiResource)
    {
        return $this->getConfig()['resources'][$apiResource->getConfigName()];
    }

    /**
     * @param ApiResource $apiResource
     * @return Definition
     */
    protected function getDefinitionForApiResource(ApiResource $apiResource)
    {
        return $this->container->getDefinition($apiResource->getResourceServiceName());
    }

    /**
     * @param ApiResource $apiResource
     * @return array
     */
    protected function getActionsForApiResource(ApiResource $apiResource)
    {
        $options = $this->getOptionsForApiResource($apiResource);
        return array_keys($options['routes']);
    }

    /**
     * @return ApiManager
     */
    protected function getManager()
    {
        return $this->container->get('bite_codes.rest_api_generator.endpoint_manager');
    }
}
