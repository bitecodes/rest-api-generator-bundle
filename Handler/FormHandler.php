<?php

namespace Fludio\ApiAdminBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Fludio\ApiAdminBundle\Exception\InvalidFormException;

class FormHandler
{
    private $om;
    private $formFactory;
    private $formType;

    public function __construct(
        ObjectManager $objectManager,
        FormFactoryInterface $formFactory,
        FormTypeInterface $formType
    )
    {
        $this->om = $objectManager;
        $this->formFactory = $formFactory;
        $this->formType = $formType;
    }

    public function processForm($object, array $parameters, $method)
    {
        $form = $this->formFactory->create(get_class($this->formType), $object, array(
            'method' => $method,
            'csrf_protection' => false,
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
}