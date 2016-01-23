<?php

namespace Fludio\ApiAdminBundle\Handler;

use Doctrine\ORM\EntityRepository;

class BaseHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;
    /**
     * @var FormHandler
     */
    private $formHandler;
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @param EntityRepository $repository
     * @param FormHandler $formHandler
     * @param $entityClass
     */
    function __construct(EntityRepository $repository, FormHandler $formHandler, $entityClass)
    {
        $this->repository = $repository;
        $this->formHandler = $formHandler;
        $this->entityClass = $entityClass;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param $id
     * @return null|object
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function post($params)
    {
        return $this->formHandler->processForm(new $this->entityClass, $params, 'POST');
    }

    /**
     * @param $entity
     * @param $params
     * @return mixed
     */
    public function update($entity, $params)
    {
        return $this->formHandler->processForm($entity, $params, 'PATCH');
    }

    /**
     * @param $entity
     * @return bool
     */
    public function delete($entity)
    {
        return $this->formHandler->delete($entity);
    }
}