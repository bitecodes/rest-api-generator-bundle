<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Actions;

class Delete extends Action
{
    protected $methods = ['DELETE'];

    protected $urlType = Action::URL_TYPE_ELEMENT;
}
