<?php

namespace BiteCodes\RestApiGeneratorBundle\Security;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CachableSecurity
{
    /**
     * @var Security
     */
    protected $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public static function __set_state(array $values)
    {
        return new self($values);
    }

    public function __call($method, $arguments)
    {
        $security = new Security($this->expression);

        if (!method_exists($security, $method)) {
            $message = sprintf("Undefined method %s in class %s.", $method, get_class($security));
            throw new Exception($message);
        }

        return call_user_func_array(array($security, $method), $arguments);
    }
}