<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Resource;

use BiteCodes\RestApiGeneratorBundle\Api\Actions\Action;
use Doctrine\Common\Inflector\Inflector;

class ApiHelper
{
    /**
     * @param string $controller
     * @return string
     */
    public static function getActionClassFromControllerName($controller)
    {
        $actionNameStart = strpos($controller, ':') + 1;
        $actionNameLength = strrpos($controller, 'Action') - $actionNameStart;

        $actionName = substr($controller, $actionNameStart, $actionNameLength);

        $actionName = Inflector::classify($actionName);

        $refl = new \ReflectionClass(Action::class);

        return $refl->getNamespaceName() . '\\' . $actionName;
    }
}
