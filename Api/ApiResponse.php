<?php

namespace Fludio\RestApiGeneratorBundle\Api;

class ApiResponse
{
    const TYPE_SUCCESS = 'success';

    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $extraData = array();
    /**
     * @var null|string
     */
    protected $type;

    /**
     * ApiResponse constructor.
     * @param mixed|string $statusCode
     * @param int $data
     * @param string $type
     */
    public function __construct($statusCode, $data, $type = self::TYPE_SUCCESS)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            $this->extraData,
            array(
                'status' => $this->statusCode,
                'type' => $this->type,
                'data' => $this->data
            )
        );
    }
}