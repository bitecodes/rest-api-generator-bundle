<?php

namespace Fludio\RestApiGeneratorBundle\DependencyInjection;

use Doctrine\Common\Inflector\Inflector;
use Fludio\RestApiGeneratorBundle\Subscriber\DateTimeFormatterSubscriber;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Fludio\RestApiGeneratorBundle\Resource\Convention;
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
class FludioRestApiGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('fludio.rest_api_generator.config', $config);

        $this->registerApiResources($container, $config['entities']);
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
        $container->setDefinition('fludio.rest_api_generator.endpoint_manager', $apiManager);

        foreach ($endpointConfig as $entity => $options) {
            $definition = $this->newApiResourceDefinition($container, $entity, $options);
            $apiManager->addMethodCall('addResource', [$definition]);
        }
    }

    private function registerListeners(ContainerBuilder $container, $listeners)
    {
        if (!empty($listeners['datetime'])) {
            $definition = new Definition(DateTimeFormatterSubscriber::class);
            $namingStrategyClass = $container->getParameter('jms_serializer.camel_case_naming_strategy.class');
            $format = $listeners['datetime'];
            $definition->setArguments([$namingStrategyClass, $format]);
            $definition->addTag('jms_serializer.event_subscriber');
            $container->setDefinition('fludio_rest_api_generator.listener.date_time_formatter_listener', $definition);
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
        $refl = new \ReflectionClass($entity);
        $container->setDefinition('fludio.rest_api_generator.' . Inflector::tableize($refl->getShortName()), $definition);
        return $definition;
    }
}
