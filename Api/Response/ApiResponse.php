<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Response;

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
     * @param array $data
     * @param string $type
     */
    public function __construct($statusCode, $data = [], $type = self::TYPE_SUCCESS)
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
     * @param $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (!isset($this->extraData[$name])) {
            return $default;
        }

        return $this->extraData[$name];
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
            $this->getApiHead(),
            $this->getSortedExtraData()
        );
    }

    /**
     * @return array
     */
    protected function getApiHead()
    {
        return [
            'status' => $this->statusCode,
            'type' => $this->type,
            'data' => $this->data
        ];
    }

    /**
     * @return array
     */
    protected function getSortedExtraData()
    {
        $keys = array_keys($this->extraData);
        sort($keys);
        return array_combine($keys, array_values($this->extraData));
    }
}