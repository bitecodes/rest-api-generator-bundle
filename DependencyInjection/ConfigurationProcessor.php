<?php

namespace BiteCodes\RestApiGeneratorBundle\DependencyInjection;

use Doctrine\Common\Util\Inflector;
use BiteCodes\RestApiGeneratorBundle\Form\DynamicFormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationProcessor
{
    public static $allActions = [
        'index', 'show', 'create', 'update', 'delete', 'batch_delete', 'batch_update'
    ];

    /**
     * @param $resourceName
     * @param $options
     * @return array
     */
    public static function resolve($resourceName, $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'only' => [],
            'except' => [],
            'resource_name' => $resourceName,
            'identifier' => 'id',
            'form_type' => DynamicFormType::class,
            'secure' => [
                'default' => []
            ],
            'filter' => null,
            'paginate' => false,
            'sub_resources' => [],
            'is_main_resource' => true
        ]);

        $resolver->setRequired('entity');

        return $resolver->resolve($options);
    }

    /**
     * @param $options
     * @return array
     */
    public static function getAvailableActions($options)
    {
        $base = !empty($options['only']) ? $options['only'] : self::$allActions;

        return array_diff($base, $options['except']);
    }

    /**
     * @param $entity
     * @return string
     */
    public static function getDefaultResourceName($entity)
    {
        $refl = new \ReflectionClass($entity);
        $name = $refl->getShortName();
        $pluralized = Inflector::pluralize($name);
        $underscored = Inflector::tableize($pluralized);

        return $underscored;
    }

    public static function getActionSecurity($options, $actionName)
    {
        if (empty($options['secure'])) {
            return [];
        }

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

        return $security[$actionName];
    }
}
