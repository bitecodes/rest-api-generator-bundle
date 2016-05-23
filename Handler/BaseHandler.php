<?php

namespace BiteCodes\RestApiGeneratorBundle\Handler;

use BiteCodes\RestApiGeneratorBundle\Filter\FilterDecorator;
use BiteCodes\RestApiGeneratorBundle\Util\EntitiesHolder;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var ApiResource[]
     */
    private $parentResources;

    /**
     * @param EntityManager $em
     * @param ApiResource $apiResource
     * @param FormHandler $formHandler
     * @param FilterInterface $filter
     */
    function __construct(EntityManager $em, ApiResource $apiResource, FormHandler $formHandler, FilterInterface $filter = null)
    {
        $repository = $em->getRepository($apiResource->getEntityClass());
        $this->repository = $this->getRepository($repository);
        $this->formHandler = $formHandler;
        $this->filter = $filter;
    }

    /**
     * @return ApiResource
     */
    public function getApiResource()
    {
        return $this->apiResource;
    }

    /**
     * @param ApiResource $apiResource
     */
    public function setApiResource($apiResource)
    {
        $this->apiResource = $apiResource;
    }

    /**
     * @return ApiResource[]
     */
    public function getParentResources()
    {
        return $this->parentResources;
    }

    /**
     * @param ApiResource[] $parentResources
     */
    public function setParentResources($parentResources)
    {
        $this->parentResources = $parentResources;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->repository->filter($this->getFilter(), $this->getParams($this->apiResource));
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
        // TODO make configurable
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
        $criteria = $this->getCriteria($id);

        $result = $this->repository->filter(
            $this->getFilter($criteria),
            $this->getParams($this->apiResource, $criteria)
        );

        switch (count($result)) {
            case 0:
                $result = null;
                break;
            case 1:
                $result = $result[0];
                break;
            default:
                // TODO provide a more meaningful message
                throw new \LogicException('Something\'s fishy');
                break;
        }

        return $result;
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
    public function create($params)
    {
        $className = $this->repository->getClassName();
        $params = $this->getParams($this->apiResource, $params, true);

        return $this->formHandler->processForm(new $className, $params, 'POST');
    }

    /**
     * @param $params
     * @return mixed
     */
    public function batchCreate($params)
    {
        $className = $this->repository->getClassName();

        return $this->formHandler->batchProcessFormCreate(new EntitiesHolder(), ['entities' => $params], 'POST', new $className);
    }

    /**
     * @param $entity
     * @param $params
     * @param $method
     * @return mixed
     */
    public function update($entity, $params, $method)
    {
        $params = $this->getParams($this->apiResource, $params, true);

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
        $className = $this->repository->getClassName();

        return $this->formHandler->batchProcessForm(new EntitiesHolder($entities), ['entities' => $params], $method, new $className);
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

    /**
     * @param array $critiera
     * @return FilterDecorator
     */
    protected function getFilter($critiera = [])
    {
        return new FilterDecorator($this->apiResource, $critiera, $this->filter);
    }

    /**
     * @param ApiResource $apiResource
     * @param array $searchParams
     * @param bool $public
     * @return array
     */
    private function getParams(ApiResource $apiResource, $searchParams = [], $public = false)
    {
        if ($parentResource = $apiResource->getParentResource()) {
            $key = $public ? $apiResource->getAssocParent() : FilterDecorator::getFilterName($parentResource);

            $searchParams[$key] = $parentResource->getIdentifierValue();

            $searchParams = array_merge($searchParams, $this->getParams($parentResource, $searchParams, $public));
        }

        return $searchParams;
    }

    /**
     * @param EntityRepository $repository
     * @return RepositoryDecorator|EntityRepository
     */
    private function getRepository(EntityRepository $repository)
    {
        $filterableEntityRepository = 'BiteCodes\DoctrineFilterBundle\Repository\FilterableEntityRepository';
        $refl = new \ReflectionClass($repository);

        if (class_exists($filterableEntityRepository)) {
            $isFilterable = $refl->isSubclassOf($filterableEntityRepository);
        } else {
            $isFilterable = false;
        }

        return $isFilterable ? $repository : new RepositoryDecorator($repository);
    }
}