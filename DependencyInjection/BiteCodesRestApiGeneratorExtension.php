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

        foreach ($endpointConfig as $entity => $options) {
            $definition = $this->newApiResourceDefinition($container, $entity, $options);
            $apiManager->addMethodCall('addResource', [$definition]);
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
     * @param $entity
     * @param $options
     * @return Definition
     */
    private function newApiResourceDefinition(ContainerBuilder $container, $entity, $options)
    {
        $definition = new Definition(ApiResource::class);
        $definition->setArguments([$entity, $options]);
        $definition->setPublic(false);
        $container->setDefinition('bite_codes.rest_api_generator.' . ConfigurationProcessor::getDefaultResourceName($entity), $definition);
        return $definition;
    }
}
