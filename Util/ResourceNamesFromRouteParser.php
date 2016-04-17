<?php

namespace BiteCodes\RestApiGeneratorBundle\Util;

class ResourceNamesFromRouteParser
{
    /**
     * @param $routeName
     * @param $bundlePrefix
     * @return array
     */
    public static function getResourceNames($routeName, $bundlePrefix)
    {
        $prefixPos = strlen($bundlePrefix . '.');
        $lastDot = strrpos($routeName, '.', $prefixPos);

        $resourceName = substr($routeName, $prefixPos, $lastDot - $prefixPos);

        $names = preg_split('/\./', $resourceName);

        return $names;
    }

    /**
     * @param $routeName
     * @param $bundlePrefix
     * @return array
     */
    public static function getSubResourceNames($routeName, $bundlePrefix)
    {
        $names = self::getResourceNames($routeName, $bundlePrefix);

        return array_slice($names, 0, count($names) - 1);
    }
}