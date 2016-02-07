<?php

namespace Fludio\RestApiGeneratorBundle\Api;

use Symfony\Component\HttpFoundation\Response;

class ApiProblem extends ApiResponse
{
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    const TYPE_ENTITY_NOT_FOUND = 'entity_not_found';

    /**
     * Problem titles
     *
     * @var array
     */
    protected static $titles = [
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        self::TYPE_ENTITY_NOT_FOUND => 'The requested entity was not found',
    ];

    /**
     * @var string
     */
    protected $title;

    /**
     * ApiProblem constructor.
     *
     * @param $statusCode
     * @param null $type
     * @param array $data
     */
    public function __construct($statusCode, $type = null, $data = [])
    {
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

        $this->title = $title;

        parent::__construct($statusCode, $data, $type);
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    protected function getApiHead()
    {
        return [
            'status' => $this->statusCode,
            'type' => $this->type,
            'title' => $this->title,
            'data' => $this->data
        ];
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'title' => $this->title
            ]
        );
    }
}
