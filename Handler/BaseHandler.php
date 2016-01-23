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
     * @param $criteria
     * @return array
     */
    public function getBy($criteria)
    {
        return $this->repository->findBy($criteria);
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
    public function update($entity, $params, $method)
    {
        return $this->formHandler->processForm($entity, $params, $method);
    }

    /**
     * @param $entities
     * @param $params
     * @param $method
     * @return bool
     */
    public function batchUpdate($entities, $params, $method)
    {
        unset($params['id']);

        foreach ($entities as $entity) {
            $this->update($entity, $params, $method);
        }

        return true;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function delete($entity)
    {
        return $this->formHandler->delete($entity);
    }

    /**
     * @param $ids
     * @return bool
     */
    public function batchDelete($ids)
    {
        foreach ($ids as $id) {
            $entity = $this->get($id);
            if ($entity) {
                $this->delete($entity);
            }
        }

        return true;
    }
}