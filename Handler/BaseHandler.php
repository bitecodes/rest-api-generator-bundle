<?php

namespace BiteCodes\RestApiGeneratorBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use BiteCodes\DoctrineFilter\FilterInterface;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Repository\RepositoryDecorator;

class BaseHandler
{
    /**
     * @var RepositoryDecorator|EntityRepository
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
     * @var ApiResource
     */
    private $apiResource;

    /**
     * @param EntityManager $em
     * @param ApiResource $apiResource
     * @param FormHandler $formHandler
     * @param FilterInterface $filter
     */
    function __construct(EntityManager $em, ApiResource $apiResource, FormHandler $formHandler, FilterInterface $filter = null)
    {
        $repository = $em->getRepository($apiResource->getEntityClass());
        $this->repository = new RepositoryDecorator($repository);
        $this->formHandler = $formHandler;
        $this->filter = $filter;
        $this->apiResource = $apiResource;
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
     * @param null $paginator
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function paginate($params, $page, $perPage = 20, &$paginator = null)
    {
        $page = $page ?: 1;
        $perPage = $perPage ?: 10;

        return $this->repository->paginate($this->filter, $params, $page, $perPage, $paginator);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function get($id)
    {
        return $this->repository->findOneBy($this->getCriteria($id));
    }

    /**
     * @param $ids
     * @return array
     */
    public function getBy($ids)
    {
        return $this->repository->findBy($this->getCriteria($ids));
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

    /**
     * @param $id
     * @return array
     */
    protected function getCriteria($id)
    {
        return [
            $this->apiResource->getIdentifier() => $id
        ];
    }
}