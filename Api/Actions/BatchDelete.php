<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Actions;

class BatchDelete extends Action
{
    protected $methods = ['DELETE'];

    protected $urlType = Action::URL_TYPE_COLLECTION;
}
