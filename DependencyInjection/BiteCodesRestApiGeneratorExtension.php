<?php

namespace BiteCodes\RestApiGeneratorBundle\DependencyInjection;

use DateTime;
use BiteCodes\RestApiGeneratorBundle\Subscriber\DateTimeFormatterSubscriber;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class BiteCodesRestApiGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('bite_codes.rest_api_generator.config', $config);

        $this->registerApiResources($container, $config['resources']);
        $this->registerListeners($container, $config['listener']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     * @param $endpointConfig
     */
    protected function registerApiResources(ContainerBuilder $container, $endpointConfig)
    {
        // Register ApiManager
        $apiManager = new Definition(ApiManager::class);
        $container->setDefinition('bite_codes.rest_api_generator.endpoint_manager', $apiManager);

        foreach ($endpointConfig as $resourceName => $options) {
            if ($options['is_main_resource']) {
                $definition = $this->newApiResourceDefinition($container, $resourceName, $resourceName, $options);
                $apiManager->addMethodCall('addResource', [$definition]);

                foreach ($options['sub_resources'] as $subResourceName => $subResourceConfig) {
                    $subDef = $this->addSubResource($container, $endpointConfig, $resourceName, $subResourceName, $apiManager);
                    $subDef->addMethodCall('setAssocParent', [$subResourceConfig['assoc_parent']]);
                    $subDef->addMethodCall('setAssocSubResource', [$subResourceConfig['assoc_sub']]);
                    $subDef->addMethodCall('setParentResource', [$definition]);
                }
                $container->setDefinition("bite_codes.rest_api_generator.$resourceName", $definition);
            }
        }
    }

    private function registerListeners(ContainerBuilder $container, $listeners)
    {
        if (!empty($listeners['datetime'])) {
            $definition = new Definition(DateTimeFormatterSubscriber::class);
            $format = $listeners['datetime'];
            $definition->setArguments([$format]);
            $definition->addTag('jms_serializer.subscribing_handler', [
                'type' => DateTime::class,
                'format' => 'json',
                'method' => 'serializeDateTimeToJson'
            ]);
            $container->setDefinition('bite_codes_rest_api_generator.listener.date_time_formatter_listener', $definition);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $resourceName
     * @param $configName
     * @param $options
     * @param bool $isSubResource
     * @return Definition
     */
    private function newApiResourceDefinition(ContainerBuilder $container, $resourceName, $configName, $options, $isSubResource = false)
    {
        $definition = new Definition(ApiResource::class);
        $definition->setArguments([$resourceName, $options]);
        $definition->setPublic(false);
        $definition->addMethodCall('setConfigName', [$configName]);
        if ($isSubResource) {
            $definition->addMethodCall('setType', [ApiResource::SUB_RESOURCE]);
        }
        return $definition;
    }

    /**
     * @param ContainerBuilder $container
     * @param $endpointConfig
     * @param $resourceName
     * @param $subResourceName
     * @param $apiManager
     * @return Definition
     */
    protected function addSubResource(ContainerBuilder $container, $endpointConfig, $resourceName, $subResourceName, $apiManager)
    {
        $name = $resourceName . '_' . $subResourceName;
        $subResourceOptions = $endpointConfig[$subResourceName];
        $definition = $this->newApiResourceDefinition($container, $name, $subResourceName, $subResourceOptions, true);
        $apiManager->addMethodCall('addResource', [$definition]);

        foreach ($subResourceOptions['sub_resources'] as $subSubResourceName => $options) {
            $subDef = $this->addSubResource($container, $endpointConfig, $name, $subSubResourceName, $apiManager);
            $subDef->addMethodCall('setAssocParent', [$options['assoc_parent']]);
            $subDef->addMethodCall('setAssocSubResource', [$options['assoc_sub']]);
            $subDef->addMethodCall('setParentResource', [$definition]);
        }

        $container->setDefinition("bite_codes.rest_api_generator.$name", $definition);

        return $definition;
    }
}
