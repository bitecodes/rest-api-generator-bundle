<?php

namespace Fludio\RestApiGeneratorBundle\Listener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

class DateTimeFormatterListener implements EventSubscriberInterface
{
    /**
     * Holds an array of object references and
     * their datetime properties
     *
     * @var array
     */
    protected $datetimes = [];
    /**
     * @var PropertyNamingStrategyInterface
     */
    protected $naming;

    public function __construct($jmsNamingStrategyClass)
    {
        $this->naming = new $jmsNamingStrategyClass;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
            ]
        ];
    }

    /**
     * Fetch all DateTime properties, store them in an array
     * and remove the metadata from the serialization
     *
     * @param PreSerializeEvent $event
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $obj = $event->getObject();
        $meta = $event->getContext()->getMetadataFactory()->getMetadataForClass(get_class($obj));

        foreach ($meta->propertyMetadata as $field => $data) {
            if ($data->type['name'] == 'DateTime') {
                $refl = $data->reflection;
                $objHash = spl_object_hash($obj);
                $fieldName = $this->naming->translateName($data);
                $this->datetimes[$objHash][$fieldName] = $refl->getValue($obj);
                unset($meta->propertyMetadata[$field]);
            }
        }
    }

    /**
     * Add data after serialization
     *
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $obj = $event->getObject();
        $objHash = spl_object_hash($obj);

        if (isset($this->datetimes[$objHash])) {
            foreach ($this->datetimes[$objHash] as $field => $datetime) {
                $value = $datetime->format('U');
                $event->getVisitor()->addData($field, $value);
            }
        }
    }
}