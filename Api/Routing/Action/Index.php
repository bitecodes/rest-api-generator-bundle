<?php

namespace Fludio\RestApiGeneratorBundle\Api\Routing\Action;

use Symfony\Component\Routing\Router;

class Index extends Action
{
    protected $methods = ['GET'];

    protected $urlType = Action::URL_TYPE_COLLECTION;

    public function getUrlSchema()
    {
        return '/' . $this->apiResource->getName();
    }
}