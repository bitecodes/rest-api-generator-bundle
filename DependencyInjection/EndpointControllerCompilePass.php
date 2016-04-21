<?php

namespace BiteCodes\RestApiGeneratorBundle\DependencyInjection;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Controller\RestApiController;
use BiteCodes\RestApiGeneratorBundle\Handler\BaseHandler;
use BiteCodes\RestApiGeneratorBundle\Handler\FormHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EndpointControllerCompilePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var ApiManager $manager */
        $manager = $container->get('bite_codes.rest_api_generator.endpoint_manager');

        foreach ($manager->getResources() as $entity => $config) {
            $this->setupEntity($config, $container);
        }
    }

    /**
     * @param ApiResource $apiResource
     * @param ContainerBuilder $container
     */
    protected function setupEntity(ApiResource $apiResource, ContainerBuilder $container)
    {
        $apiResourceServiceName = $apiResource->getResourceServiceName();
        $formHandlerServiceName = $apiResource->getFormHandlerServiceName();
        $entityHandlerServiceName = $apiResource->getEntityHandlerServiceName();
        $controllerServiceName = $apiResource->getControllerServiceName();
        $filterServiceName = $apiResource->getFilterServiceName();
        $filterClass = $apiResource->getFilterClass();

        // Filter
        if ($filterClass) {
            $filter = new Definition($filterClass);
            $container->setDefinition($filterServiceName, $filter);
        }

        // Form Handler
        $formHandler = new Definition(FormHandler::class);
        $formHandler->addArgument(new Reference('doctrine.orm.entity_manager'));
        $formHandler->addArgument(new Reference('form.factory'));
        $formHandler->addArgument($apiResource->getFormTypeClass());
        $container->setDefinition($formHandlerServiceName, $formHandler);

        // Handler
        $entityHandler = new Definition(BaseHandler::class);
        $entityHandler->addArgument(new Reference('doctrine.orm.entity_manager'));
        $entityHandler->addArgument(new Reference($apiResourceServiceName));
        $entityHandler->addArgument(new Reference($formHandlerServiceName));
        if ($filterClass) {
            $entityHandler->addArgument(new Reference($filterServiceName));
        }
        $container->setDefinition($entityHandlerServiceName, $entityHandler);

        // Controller
        $controller = new Definition(RestApiController::class);
        $controller->addMethodCall('setContainer', [new Reference('service_container')]);
        $controller->addMethodCall('setHandler', [new Reference($entityHandlerServiceName)]);
        $container->setDefinition($controllerServiceName, $controller);
    }
}