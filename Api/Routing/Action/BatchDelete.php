<?php

namespace Fludio\RestApiGeneratorBundle\Api\Routing\Action;

class BatchDelete extends Action
{
    protected $methods = ['DELETE'];

    protected $urlType = Action::URL_TYPE_COLLECTION;

}
