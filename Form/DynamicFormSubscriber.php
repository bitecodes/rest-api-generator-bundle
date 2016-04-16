<?php

namespace BiteCodes\RestApiGeneratorBundle\Form;

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
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $classMetadata;

    /**
     * DynamicFormSubscriber constructor.
     *
     * @param EntityManager $em
     * @param $object
     */
    public function __construct(EntityManager $em, $object)
    {
        $this->classMetadata = $em->getClassMetadata(get_class($object));
    }

    /**
     * @return array
     */
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
        $mappings = $this->classMetadata->fieldMappings;

        foreach ($this->getFields() as $field) {
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
     * @return array $fields
     */
    public function getFields()
    {
        $fields = (array)$this->classMetadata->fieldNames;

        // Remove the primary key field if it's not managed manually
        if (!$this->classMetadata->isIdentifierNatural()) {
            $fields = array_diff($fields, $this->classMetadata->identifier);
        }

        foreach ($this->classMetadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] !== ClassMetadataInfo::ONE_TO_MANY) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }
}
