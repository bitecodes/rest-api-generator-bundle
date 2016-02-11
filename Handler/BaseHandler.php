<?php

namespace Fludio\RestApiGeneratorBundle\Handler;

use Doctrine\ORM\EntityManager;
use Fludio\DoctrineFilter\FilterInterface;
use Fludio\RestApiGeneratorBundle\Repository\RepositoryDecorator;

class BaseHandler
{
    /**
     * @var RepositoryDecorator
     */
    private $repository;
    /**
     * @var FormHandler
     */
    private $formHandler;
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @param EntityManager $em
     * @param $entityClass
     * @param FormHandler $formHandler
     * @param FilterInterface $filter
     */
    function __construct(EntityManager $em, $entityClass, FormHandler $formHandler, FilterInterface $filter = null)
    {
        $repository = $em->getRepository($entityClass);
        $this->repository = new RepositoryDecorator($repository);
        $this->formHandler = $formHandler;
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @param $params
     * @return array
     */
    public function filter($params)
    {
        return $this->repository->filter($this->filter, $params);
    }

    /**
     * @param $params
     * @param int $page
     * @param int $perPage
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function paginate($params, $page = 1, $perPage = 20, &$paginator = null)
    {
        return $this->repository->paginate($this->filter, $params, $page, $perPage, $paginator);
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
        $className = $this->repository->getClassName();
        return $this->formHandler->processForm(new $className, $params, 'POST');
    }

    /**
     * @param $entity
     * @param $params
     * @param $method
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
    public function batchDelete(array $ids)
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