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
            $refl = new \ReflectionClass($apiResource->getEntityClass());
            $name = $refl->getShortName();
            $def = $container->getDefinition('fludio.rest_api_generator.' . Inflector::tableize($name));
            $options = $config['entities'][$apiResource->getEntityClass()];
            $base = !empty($options['only']) ? $options['only'] : self::$allActions;
            $actions = array_diff($base, $options['except']);

            foreach ($actions as $actionName) {
                $actionClass = 'Fludio\RestApiGeneratorBundle\Api\Actions\\' . Inflector::classify($actionName);
                $action = new Definition($actionClass);
                $action->addArgument(new Reference('router'));
                if ($roles = $this->getActionSecurity($options)) {
                    $action->addMethodCall('setRoles', [$roles[$actionName]]);
                }
                $container->set('fludio.rest_api_generator.action.' . $apiResource->getName() . '.' . Inflector::tableize($actionName), $action);
                $def->addMethodCall('addAction', [$action]);
            }
        }
    }

    protected static function getActionSecurity($options)
    {
        if (isset($options['secure'])) {
            $secure = $options['secure'];

            $defaultSecurity = isset($secure['default']) ? $secure['default'] : [];

            $security = array_reduce(self::$allActions, function ($acc, $action) use ($defaultSecurity) {
                $acc[$action] = $defaultSecurity;
                return $acc;
            }, []);

            if (isset($secure['routes'])) {
                foreach ($secure['routes'] as $action => $roles) {
                    $security[$action] = $roles;
                }
            }

            return $security;
        }
    }
}
