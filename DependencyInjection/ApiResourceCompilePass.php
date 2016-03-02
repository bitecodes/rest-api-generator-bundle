<?php

namespace Fludio\RestApiGeneratorBundle\DependencyInjection;

use Doctrine\Common\Inflector\Inflector;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ApiResourceCompilePass implements CompilerPassInterface
{
    public static $allActions = [
        'index', 'show', 'create', 'update', 'delete', 'batch_delete', 'batch_update'
    ];

    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('fludio.rest_api_generator.config');
        /** @var ApiManager $manager */
        $manager = $container->get('fludio.rest_api_generator.endpoint_manager');

        foreach ($manager->getResources() as $apiResource) {
            $def = $container->getDefinition($apiResource->getResourceServiceName());
            $options = $config['entities'][$apiResource->getEntityClass()];
            $base = !empty($options['only']) ? $options['only'] : self::$allActions;
            $actions = array_diff($base, $options['except']);

            foreach ($actions as $actionName) {
                $class = Inflector::classify($actionName);
                $actionClass = "Fludio\\RestApiGeneratorBundle\\Api\\Actions\\$class";
                $action = new Definition($actionClass);
                $action->addArgument(new Reference('router'));
                $action->addMethodCall('setRoles', [ConfigurationProcessor::getActionSecurity($options, $actionName)]);
                $container->set('fludio.rest_api_generator.action.' . $apiResource->getName() . '.' . Inflector::tableize($actionName), $action);
                $def->addMethodCall('addAction', [$action]);
            }
        }
    }
}
