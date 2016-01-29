<?php

namespace Fludio\RestApiGeneratorBundle\Resource;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceOptions
{
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
            'resource_name' => self::getDefaultResourceName($entity)
        ]);

        return $resolver->resolve($options);
    }

    /**
     * @param $options
     * @return array
     */
    public static function getAvailableActions($options)
    {
        $all = [
            ResourceActionData::ACTION_INDEX,
            ResourceActionData::ACTION_SHOW,
            ResourceActionData::ACTION_CREATE,
            ResourceActionData::ACTION_UPDATE,
            ResourceActionData::ACTION_BATCH_UPDATE,
            ResourceActionData::ACTION_DELETE,
            ResourceActionData::ACTION_BATCH_DELETE
        ];

        $base = !empty($options['only']) ? $options['only'] : $all;

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
}