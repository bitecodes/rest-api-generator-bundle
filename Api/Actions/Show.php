<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Actions;

class Show extends Action
{
    protected $methods = ['GET'];

    protected $urlType = Action::URL_TYPE_ELEMENT;
}
