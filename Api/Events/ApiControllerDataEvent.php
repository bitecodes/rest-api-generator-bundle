<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Events;

use Symfony\Component\EventDispatcher\Event;

class ApiControllerDataEvent extends Event
{
    /**
     * @var string
     */
    private $resourceName;

    /**
     * @var mixed
     */
    protected $data;

    public function __construct($resourceName, $data)
    {
        $this->resourceName = $resourceName;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @param string $resourceName
     *
     * @return ApiControllerDataEvent
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}