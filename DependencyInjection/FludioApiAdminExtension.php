<?php

namespace Fludio\ApiAdminBundle\DependencyInjection;

use Doctrine\Common\Inflector\Inflector;
use Fludio\ApiAdminBundle\Resource\ResourceManager;
use Fludio\ApiAdminBundle\Resource\Resource;
use Fludio\ApiAdminBundle\Resource\Convention;
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
class FludioApiAdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $endpointConfig = $config['entities'];

        $endpointManager = new Definition(ResourceManager::class);
        $container->setDefinition('fludio.api_admin.endpoint_manager', $endpointManager);

        foreach ($endpointConfig as $entity => $options) {
            $definition = $this->newEntityEndpointConfigurationDefinition($container, $entity, $options);
            $endpointManager->addMethodCall('addConfiguration', [$definition]);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     * @param $entity
     * @param $options
     * @return Definition
     */
    private function newEntityEndpointConfigurationDefinition(ContainerBuilder $container, $entity, $options)
    {
        $definition = new Definition(Resource::class);
        $definition->setArguments([$entity, $options]);
        $definition->setPublic(false);
        $refl = new \ReflectionClass($entity);
        $container->setDefinition('fludio.api_admin.' . Inflector::tableize($refl->getShortName()), $definition);
        return $definition;
    }
}
