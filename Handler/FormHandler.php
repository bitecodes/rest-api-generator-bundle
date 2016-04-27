<?php

namespace BiteCodes\RestApiGeneratorBundle\Handler;

use BiteCodes\RestApiGeneratorBundle\Form\DynamicFormType;
use Doctrine\Common\Persistence\ObjectManager;
use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiProblem;
use BiteCodes\RestApiGeneratorBundle\Exception\ApiProblemException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

class FormHandler
{
    /**
     * @var ObjectManager
     */
    private $om;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var FormTypeInterface
     */
    private $formType;

    /**
     * FormHandler constructor.
     * @param ObjectManager $objectManager
     * @param FormFactoryInterface $formFactory
     * @param $formType
     */
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

    /**
     * @param $object
     * @param array $parameters
     * @param $method
     * @return mixed
     */
    public function processForm($object, array $parameters, $method)
    {
        $entity = $this
            ->process($parameters, $method, $object)
            ->getData();

        $this->om->persist($entity);
        $this->om->flush();

        return $entity;
    }

    /**
     * @param $objects
     * @param array $parameters
     * @param $method
     * @return array
     */
    public function batchProcessForm($objects, array $parameters, $method)
    {
        $data = [];
        $errors = [];

        foreach ($objects as $object) {
            try {
                $entity = $this
                    ->process($parameters, $method, $object)
                    ->getData();

                $data[] = $entity;
                $this->om->persist($entity);
            } catch (ApiProblemException $e) {
                $meta = $this->om->getClassMetadata(get_class($object));
                $id = $meta->getIdentifierValues($object);
                $errors[array_values($id)[0]] = $e->getApiProblem()->get('errors');
            }

        }

        if (count($errors) > 0) {
            throw $this->createValidationErrorException($errors);
        }

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

    /**
     * @param array $parameters
     * @param $method
     * @param $object
     * @return FormInterface
     */
    protected function process(array $parameters, $method, $object)
    {
        $form = $this->formFactory->create(
            $this->formType,
            $object,
            $this->getOptions($object, $method)
        );

        $form->submit($parameters, 'PATCH' !== $method);

        if (!$form->isValid()) {
            throw $this->createValidationErrorException($this->getErrorsFromForm($form));
        }

        return $form;
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

    /**
     * @param $object
     * @param $method
     * @return array
     */
    protected function getOptions($object, $method)
    {
        $options = [
            'method' => $method,
            'csrf_protection' => false,
        ];


        if (DynamicFormType::class === $this->formType) {
            $options['object'] = $object;
        }

        return $options;
    }

    /**
     * @param $errors
     * @return ApiProblemException
     */
    protected function createValidationErrorException($errors)
    {
        $problem = new ApiProblem(
            422,
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $problem->set('errors', $errors);
        return new ApiProblemException($problem);
    }
}