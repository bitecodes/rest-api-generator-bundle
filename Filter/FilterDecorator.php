<?php

namespace BiteCodes\RestApiGeneratorBundle\Filter;

use BiteCodes\DoctrineFilter\FilterBuilder;
use BiteCodes\DoctrineFilter\FilterInterface;
use BiteCodes\DoctrineFilter\Type\EqualFilterType;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Doctrine\ORM\Mapping\ClassMetadata;

class FilterDecorator implements FilterInterface
{
    /**
     * @var FilterInterface
     */
    private $filter;
    /**
     * @var ApiResource[]
     */
    private $parentResources;
    /**
     * @var array
     */
    private $criteria;

    public function __construct($parentResources, $criteria = [], $filter = null)
    {
        if (!$filter) {
            $filter = new EmptyFilter();
        }

        $this->filter = $filter;
        $this->parentResources = $parentResources ?: [];
        $this->criteria = $criteria;
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
        $em = $qb->getEntityManager();
        $rootEntity = $qb->getRootEntities()[0];

        $meta = $em->getClassMetadata($rootEntity);

        foreach ($this->parentResources as $resource) {
            $mappings = $this->getAssociationMappings($meta, $resource->getEntityClass());

            $fields = array_map(function ($mapping) use ($resource) {
                $values = array_values($mapping['sourceToTargetKeyColumns']);
                return $mapping['fieldName'] . '.' . $values[0];
            }, $mappings);

            $builder->add(self::getFilterName($resource), EqualFilterType::class, [
                'fields' => array_values($fields)
            ]);
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
}