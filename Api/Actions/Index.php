<?php

namespace Fludio\RestApiGeneratorBundle\Api\Actions;

class Index extends Action
{
    const METHOD_PAGINATION = 'paginate';
    const METHOD_FILTER = 'filter';
    const METHOD_ALL = 'all';

    /**
     * @var array
     */
    protected $methods = ['GET'];

    /**
     * @var string
     */
    protected $urlType = Action::URL_TYPE_COLLECTION;

    /**
     * @return string
     */
    public function getResourceGetterMethod()
    {
        if ($this->apiResource->hasPagination()) {
            $method = self::METHOD_PAGINATION;
        } elseif ($this->apiResource->getFilterClass()) {
            $method = self::METHOD_FILTER;
        } else {
            $method = self::METHOD_ALL;
        }

        return $method;
    }
}
