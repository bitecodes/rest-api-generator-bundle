<?php

namespace BiteCodes\RestApiGeneratorBundle\Filter;

use BiteCodes\DoctrineFilter\FilterBuilder;
use BiteCodes\DoctrineFilter\FilterInterface;
use BiteCodes\DoctrineFilter\Type\EqualFilterType;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

class FilterDecorator implements FilterInterface
{
    /**
     * @var FilterInterface
     */
    private $filter;
    /**
     * @var ApiResource
     */
    private $apiResource;
    /**
     * @var array
     */
    private $criteria;
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * FilterDecorator constructor.
     * @param ApiResource $apiResource
     * @param array $criteria
     * @param null $filter
     */
    public function __construct(ApiResource $apiResource, $criteria = [], $filter = null)
    {
        if (!$filter) {
            $filter = new EmptyFilter();
        }

        $this->apiResource = $apiResource;
        $this->criteria = $criteria;
        $this->filter = $filter;
    }

    public static function getFilterName(ApiResource $resource)
    {
        return $resource->getBundlePrefix() . '_' . $resource->getName();
    }

    /**
     * @param FilterBuilder $builder
     */
    public function buildFilter(FilterBuilder $builder)
    {
        $this->addFiltersForParentResources($builder);
        $this->addFiltersForCriteria($builder);

        $this->filter->buildFilter($builder);
    }

    protected function addFiltersForParentResources(FilterBuilder $builder)
    {
        $qb = $builder->getQueryBuilder();
        $this->em = $qb->getEntityManager();
        $rootEntity = $qb->getRootEntities()[0];

        $meta = $this->em->getClassMetadata($rootEntity);

        if ($parentResource = $this->apiResource->getParentResource()) {
            $this->addFilterForParent($builder, $meta, $parentResource);
        }
    }

    /**
     * @param FilterBuilder $builder
     */
    protected function addFiltersForCriteria(FilterBuilder $builder)
    {
        foreach ($this->criteria as $field => $value) {
            $builder->add($field, EqualFilterType::class);
        }
    }

    /**
     * @param $meta
     * @param $entityClass
     * @return array
     */
    protected function getAssociationMappings(ClassMetadata $meta, $entityClass)
    {
        return array_filter($meta->associationMappings, function ($mapping) use ($entityClass) {
            return $mapping['targetEntity'] == $entityClass;
        });
    }

    /**
     * @param FilterBuilder $builder
     * @param $meta
     * @param $parentResource
     */
    protected function addFilterForParent(FilterBuilder $builder, $meta, ApiResource $parentResource, $prefix = '')
    {
        $mappings = $this->getAssociationMappings($meta, $parentResource->getEntityClass());

        $fields = array_map(function ($mapping) use ($prefix) {
            $values = array_values($mapping['sourceToTargetKeyColumns']);
            return $prefix . $mapping['fieldName'] . '.' . $values[0];
        }, $mappings);

        $builder->add(self::getFilterName($parentResource), EqualFilterType::class, [
            'fields' => array_values($fields)
        ]);

        if ($parentsParent = $parentResource->getParentResource()) {
            $meta = $this->em->getClassMetadata($parentResource->getEntityClass());
            $this->addFilterForParent($builder, $meta, $parentsParent, $parentResource->getAssocSubResource() . '.');
        }
    }
}