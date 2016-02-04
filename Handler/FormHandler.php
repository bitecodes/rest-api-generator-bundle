<?php

namespace Fludio\RestApiGeneratorBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Fludio\RestApiGeneratorBundle\Exception\InvalidFormException;

class FormHandler
{
    private $om;
    private $formFactory;
    private $formType;

    public function __construct(
        ObjectManager $objectManager,
        FormFactoryInterface $formFactory,
        $formType
    )
    {
        $this->om = $objectManager;
        $this->formFactory = $formFactory;
        $this->formType = $formType;
    }

    public function processForm($object, array $parameters, $method)
    {
        $form = $this->formFactory->create($this->formType, $object, array(
            'method' => $method,
            'csrf_protection' => false,
            'object' => $object
        ));

        $form->submit($parameters, 'PATCH' !== $method);

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $data = $form->getData();
        $this->om->persist($data);
        $this->om->flush();

        return $data;
    }

    public function delete($object)
    {
        $this->om->remove($object);
        $this->om->flush();

        return true;
    }

    public function batchDelete($ids)
    {
        foreach ($ids as $id) {
            $entity = $this->om->find($id);
            $this->om->remove($entity);
        }

        $this->om->flush();
    }

    public function getFormTypeClass()
    {
        return $this->formType;
    }
}