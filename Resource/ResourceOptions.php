<?php

namespace Fludio\RestApiGeneratorBundle\Resource;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceOptions
{
    public static $allActions = [
        ResourceActionData::ACTION_INDEX,
        ResourceActionData::ACTION_SHOW,
        ResourceActionData::ACTION_CREATE,
        ResourceActionData::ACTION_UPDATE,
        ResourceActionData::ACTION_BATCH_UPDATE,
        ResourceActionData::ACTION_DELETE,
        ResourceActionData::ACTION_BATCH_DELETE
    ];

    /**
     * @param $entity
     * @param $options
     * @return array
     */
    public static function resolve($entity, $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'only' => [],
            'except' => [],
            'resource_name' => self::getDefaultResourceName($entity),
            'secure' => [
                'default' => []
            ],
            'filter' => null,
            'paginate' => false
        ]);

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
    private static function getDefaultResourceName($entity)
    {
        $refl = new \ReflectionClass($entity);
        $name = $refl->getShortName();
        $pluralized = Inflector::pluralize($name);
        $underscored = Inflector::tableize($pluralized);

        return $underscored;
    }

    public static function getActionSecurity($options)
    {
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
