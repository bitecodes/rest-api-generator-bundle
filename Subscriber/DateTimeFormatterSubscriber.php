<?php

namespace Fludio\RestApiGeneratorBundle\Subscriber;

use DateTime;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

class DateTimeFormatterSubscriber implements SubscribingHandlerInterface
{
    protected $formatOptions = [
        'unix' => 'U',
        'unixmilli' => 'U000'
    ];

    /**
     * @var string
     */
    private $format;

    public function __construct($format)
    {
        $this->format = isset($this->formatOptions[$format]) ? $this->formatOptions[$format] : $format;
    }

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'DateTime',
                'method' => 'serializeDateTimeToJson',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param DateTime $date
     * @param array $type
     * @param Context $context
     * @return string
     */
    public function serializeDateTimeToJson(JsonSerializationVisitor $visitor, DateTime $date, array $type, Context $context)
    {
        return $date->format($this->format);
    }
}
