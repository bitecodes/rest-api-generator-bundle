<?php

namespace Fludio\RestApiGeneratorBundle\Api\Actions;

class Create extends Action
{
    protected $methods = ['POST'];

    protected $urlType = Action::URL_TYPE_COLLECTION;
}
