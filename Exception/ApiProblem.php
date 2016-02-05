<?php

namespace Fludio\RestApiGeneratorBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

class ApiProblem
{
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    const TYPE_ENTITY_NOT_FOUND = 'entity_not_found';

    /**
     * Problem titles
     *
     * @var array
     */
    protected static $titles = array(
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        self::TYPE_ENTITY_NOT_FOUND => 'The requested entity was not found',
    );

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var null|string
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $extraData = array();

    /**
     * ApiProblem constructor.
     *
     * @param $statusCode
     * @param null $type
     */
    public function __construct($statusCode, $type = null)
    {
        $this->statusCode = $statusCode;

        if ($type === null) {
            // no type? The default is about:blank and the title should
            // be the standard status code message
            $type = 'about:blank';
            $title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Unknown status code :(';
        } else {
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException('No title for type ' . $type);
            }

            $title = self::$titles[$type];
        }

        $this->type = $type;
        $this->title = $title;
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
                'title' => $this->title,
            )
        );
    }
}
