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

    /**
     * @param ApiResource $resource
     * @return string
     */
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
     * Add Filters for parent resource
     *
     * @param FilterBuilder $builder
     */
    protected function addFiltersForParentResources(FilterBuilder $builder)
    {
        if ($parentResource = $this->apiResource->getParentResource()) {
            $qb = $builder->getQueryBuilder();
            $em = $qb->getEntityManager();
            $rootEntity = $qb->getRootEntities()[0];

            $this->addFilterForParent($builder, $rootEntity, $em, $parentResource);
        }
    }

    /**
     * @param FilterBuilder $builder
     * @param $entity
     * @param EntityManager $em
     * @param ApiResource $parentResource
     * @param string $prefix
     */
    protected function addFilterForParent(FilterBuilder $builder, $entity, EntityManager $em, ApiResource $parentResource, $prefix = '')
    {
        $meta = $em->getClassMetadata($entity);

        $mappings = $this->getAssociationMappings($meta, $parentResource->getEntityClass());

        $builder->add(self::getFilterName($parentResource), EqualFilterType::class, [
            'fields' => $this->getFields($prefix, $mappings)
        ]);

        if ($parentsParent = $parentResource->getParentResource()) {
            $this->addFilterForParent($builder, $parentResource->getEntityClass(), $em, $parentsParent, $parentResource->getAssocSubResource() . '.');
        }
    }

    /**
     * Returns the mappings that are related to the given class
     *
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
     * Returns the fields to perform the query on
     *
     * @param $prefix
     * @param $mappings
     * @return array
     */
    protected function getFields($prefix, $mappings)
    {
        $fields = array_map(function ($mapping) use ($prefix) {
            $values = array_values($mapping['sourceToTargetKeyColumns']);
            return $prefix . $mapping['fieldName'] . '.' . $values[0];
        }, $mappings);

        return array_values($fields);
    }
}