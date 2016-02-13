<?php

namespace Fludio\RestApiGeneratorBundle\Api\Routing\Action;

class BatchUpdate extends Action
{
    protected $methods = ['PUT', 'PATCH'];

    protected $urlType = Action::URL_TYPE_COLLECTION;

}
