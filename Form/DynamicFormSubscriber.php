<?php

namespace Fludio\RestApiGeneratorBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DynamicFormSubscriber implements EventSubscriberInterface
{
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

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if (is_string($this->object)) {
            var_dump($this->object);
            die();
        }
        $meta = $this->em->getClassMetadata(get_class($this->object));
        $fields = $this->getFieldsFromMetadata($meta);
        $mappings = $meta->fieldMappings;

        foreach ($fields as $field) {
//            if (isset($mappings[$field]) && in_array($mappings[$field]['type'], ['date', 'time', 'datetime'])) {
//                $form->add($field, $mappings[$field]['type']);
//            } else {
            $form->add($field);
//            }
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
