<?php

namespace Fludio\RestApiGeneratorBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DynamicFormSubscriber implements EventSubscriberInterface
{
    protected $typeDict = [
        'datetime' => DateTimeType::class,
        'date' => DateType::class,
        'time' => TimeType::class,
    ];

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var
     */
    private $object;

    public function __construct(EntityManager $em, $object)
    {
        $this->object = $object;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $meta = $this->em->getClassMetadata(get_class($this->object));
        $fields = $this->getFieldsFromMetadata($meta);
        $mappings = $meta->fieldMappings;

        foreach ($fields as $field) {
            if (isset($mappings[$field]) && in_array($mappings[$field]['type'], array_keys($this->typeDict))) {
                $typeClass = $this->typeDict[$mappings[$field]['type']];
                $form->add($field, $typeClass, ['widget' => 'single_text']);
            } else {
                $form->add($field);
            }
        }
    }

    /**
     * Returns an array of fields. Fields can be both column fields and
     * association fields.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return array $fields
     */
    protected function getFieldsFromMetadata(ClassMetadataInfo $metadata)
    {
        $fields = (array)$metadata->fieldNames;

        // Remove the primary key field if it's not managed manually
        if (!$metadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $metadata->identifier);
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }
}
