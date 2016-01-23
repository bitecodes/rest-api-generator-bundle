<?php

namespace Fludio\ApiAdminBundle\Util;

class RouteHelper
{
    public static function pathAll($entity)
    {
        return '/' . self::snake_case($entity);
    }

    public static function pathSingle($entity)
    {
        return self::pathAll($entity) . '/{id}';
    }

    public static function action($action, $entity)
    {
        return self::controllerService($entity) . ':' . $action . 'Action';
    }

    public static function snake_case($entity)
    {
        $refl = new \ReflectionClass($entity);

        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $refl->getShortName())), '_');
    }

    public static function name($action, $entity)
    {
        return 'api_' . $action . '_' . self::snake_case($entity);
    }

    private static function controllerService($entity)
    {
        return 'fludio_api_admin.controller.' . self::snake_case($entity);
    }
}