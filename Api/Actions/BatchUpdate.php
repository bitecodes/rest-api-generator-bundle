<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Actions;

class BatchUpdate extends Action
{
    protected $methods = ['PUT', 'PATCH'];

    protected $urlType = Action::URL_TYPE_COLLECTION;
}
