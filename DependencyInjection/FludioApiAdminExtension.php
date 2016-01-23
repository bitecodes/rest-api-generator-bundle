<?php

namespace Fludio\ApiAdminBundle\DependencyInjection;

use Doctrine\ORM\EntityRepository;
use Fludio\ApiAdminBundle\Configuration\Configuration as ApiConfig;
use Fludio\ApiAdminBundle\Configuration\Convention;
use Fludio\ApiAdminBundle\Controller\RestApiController;
use Fludio\ApiAdminBundle\Handler\BaseHandler;
use Fludio\ApiAdminBundle\Handler\FormHandler;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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

        $e = $config['entities'];

        $container->setParameter('api_admin.entites', $e);
        $convention = $this->createConvention($container);

        foreach ($e as $entity) {
            $apiConfig = new ApiConfig($entity, $convention);
            $this->setupEntity($apiConfig, $container);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    protected function createConvention(ContainerBuilder $container)
    {
        $options = [
            'bundlePrefix' => 'fludio.api_admin'
        ];
        $convention = new Definition(Convention::class);
        $convention->addArgument($options);

        $container->setDefinition('fludio.api_admin.convention', $convention);

        return new Convention($options);
    }

    /**
     * @param ApiConfig $apiConfig
     * @param ContainerBuilder $container
     */
    protected function setupEntity(ApiConfig $apiConfig, ContainerBuilder $container)
    {
        // Repo
        $repo = new Definition(EntityRepository::class);
        $repo->addArgument($apiConfig->getEntityNamespace());
        $repo->setFactory([new Reference('doctrine.orm.entity_manager'), 'getRepository']);
        $container->setDefinition($apiConfig->getRepositoryServiceName(), $repo);

        // Form Handler
        $formHandler = new Definition(FormHandler::class);
        $formHandler->addArgument(new Reference('doctrine.orm.entity_manager'));
        $formHandler->addArgument(new Reference('form.factory'));
        $formHandler->addArgument(new Reference('fludio_api_admin.form.' . $apiConfig->getResourceName() . '_type'));
        $container->setDefinition($apiConfig->getFormHandlerServiceName(), $formHandler);

        // Handler
        $entityHandler = new Definition(BaseHandler::class);
        $entityHandler->addArgument(new Reference($apiConfig->getRepositoryServiceName()));
        $entityHandler->addArgument(new Reference($apiConfig->getFormHandlerServiceName()));
        $entityHandler->addArgument($apiConfig->getEntityNamespace());
        $container->setDefinition($apiConfig->getEntityHandlerServiceName(), $entityHandler);

        // Controller
        $controller = new Definition(RestApiController::class);
        $controller->addArgument(new Reference($apiConfig->getEntityHandlerServiceName()));
        $container->setDefinition($apiConfig->getControllerServiceName(), $controller);
    }
}
