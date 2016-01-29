<?php

namespace Fludio\RestApiGeneratorBundle\DependencyInjection;

use Doctrine\ORM\EntityRepository;
use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Fludio\RestApiGeneratorBundle\Controller\RestApiController;
use Fludio\RestApiGeneratorBundle\Handler\BaseHandler;
use Fludio\RestApiGeneratorBundle\Handler\FormHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EndpointControllerCompilePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var ResourceManager $manager */
        $manager = $container->get('fludio.rest_api_generator.endpoint_manager');

        foreach ($manager->getConfigurations() as $entity => $config) {
            $this->setupEntity($config, $container);
        }
    }

    /**
     * @param Resource $entityConfig
     * @param ContainerBuilder $container
     */
    protected function setupEntity(Resource $entityConfig, ContainerBuilder $container)
    {
        $repoServiceName = $entityConfig->getServices()->getRepositoryServiceName();
        $formHandlerServiceName = $entityConfig->getServices()->getFormHandlerServiceName();
        $entityHandlerServiceName = $entityConfig->getServices()->getEntityHandlerServiceName();
        $controllerServiceName = $entityConfig->getServices()->getControllerServiceName();

        // Repo
        $repo = new Definition(EntityRepository::class);
        $repo->addArgument($entityConfig->getEntityNamespace());
        $repo->setFactory([new Reference('doctrine.orm.entity_manager'), 'getRepository']);
        $container->setDefinition($repoServiceName, $repo);

        // Form Handler
        $formHandler = new Definition(FormHandler::class);
        $formHandler->addArgument(new Reference('doctrine.orm.entity_manager'));
        $formHandler->addArgument(new Reference('form.factory'));
        $formHandler->addArgument(new Reference('fludio_rest_api_generator.form.' . $entityConfig->getName() . '_type'));
        $container->setDefinition($formHandlerServiceName, $formHandler);

        // Handler
        $entityHandler = new Definition(BaseHandler::class);
        $entityHandler->addArgument(new Reference($repoServiceName));
        $entityHandler->addArgument(new Reference($formHandlerServiceName));
        $entityHandler->addArgument($entityConfig->getEntityNamespace());
        $container->setDefinition($entityHandlerServiceName, $entityHandler);

        // Controller
        $controller = new Definition(RestApiController::class);
        $controller->addMethodCall('setContainer', [new Reference('service_container')]);
        $controller->addMethodCall('setHandler', [new Reference($entityHandlerServiceName)]);
        $container->setDefinition($controllerServiceName, $controller);
    }
}