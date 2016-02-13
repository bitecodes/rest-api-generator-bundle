<?php

namespace Fludio\RestApiGeneratorBundle\Api\Routing\Action;

class Update extends Action
{
    protected $methods = ['PUT', 'PATCH'];

    protected $urlType = Action::URL_TYPE_ELEMENT;

}
