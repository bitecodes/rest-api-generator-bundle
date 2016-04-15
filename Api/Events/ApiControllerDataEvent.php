<?php

namespace Fludio\RestApiGeneratorBundle\Api\Events;

use Symfony\Component\EventDispatcher\Event;

class ApiControllerDataEvent extends Event
{
    /**
     * @var mixed
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
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