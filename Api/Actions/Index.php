<?php

namespace Fludio\RestApiGeneratorBundle\Api\Actions;

class Index extends Action
{
    protected $methods = ['GET'];

    protected $urlType = Action::URL_TYPE_COLLECTION;
}
