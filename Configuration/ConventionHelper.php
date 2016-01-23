<?php

namespace Fludio\ApiAdminBundle\Configuration;

class ConventionHelper
{
    /**
     * Return string in snake_case
     *
     * @param $string
     * @return string
     */
    public static function snakeCase($string)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_');
    }
}