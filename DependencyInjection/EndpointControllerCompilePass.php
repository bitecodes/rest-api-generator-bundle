<?php

namespace Fludio\ApiAdminBundle\DependencyInjection;

use Doctrine\ORM\EntityRepository;
use Fludio\ApiAdminBundle\Resource\ResourceManager;
use Fludio\ApiAdminBundle\Resource\Resource;
use Fludio\ApiAdminBundle\Controller\RestApiController;
use Fludio\ApiAdminBundle\Handler\BaseHandler;
use Fludio\ApiAdminBundle\Handler\FormHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EndpointControllerCompilePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var ResourceManager $manager */
        $manager = $container->get('fludio.api_admin.endpoint_manager');

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
        $formHandler->addArgument(new Reference('fludio_api_admin.form.' . $entityConfig->getName() . '_type'));
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