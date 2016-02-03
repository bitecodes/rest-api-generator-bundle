<?php

namespace Fludio\RestApiGeneratorBundle\Handler;

use Doctrine\ORM\EntityRepository;
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
     * @param EntityRepository $repository
     * @param FormHandler $formHandler
     * @param FilterInterface $filter
     */
    function __construct(EntityRepository $repository, FormHandler $formHandler, FilterInterface $filter = null)
    {
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
     * @param int $offset
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function paginate($params, $offset = 0, $limit = 20)
    {
        return $this->repository->paginate($this->filter, $params, $offset, $limit);
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