<?php

namespace Fludio\RestApiGeneratorBundle\Api\Resource;

use Doctrine\Common\Inflector\Inflector;
use Fludio\DoctrineFilter\FilterInterface;
use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Resource\ResourceOptions;
use Fludio\RestApiGeneratorBundle\Api\Resource\Traits\ServiceNames;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiResource
{
    use ServiceNames;

    /**
     * @var ApiManager
     */
    protected $manager;
    /**
     * The resource name
     *
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $filterClass;
    /**
     * @var string
     */
    protected $formTypeClass;
    /**
     * @var boolean
     */
    protected $paginate;
    /**
     * @var string
     */
    private $entity;
    /**
     * @var ResourceActionData
     */
    protected $actions;
    /**
     * @var ServiceNames
     */
    protected $services;

    public function __construct($entity, array $options = [])
    {
        $options = ResourceOptions::resolve($entity, $options);

        $this->name = $options['resource_name'];
        $this->entity = $entity;
        $this->filterClass = $options['filter'];
        $this->paginate = $options['paginate'];
        $this->formTypeClass = $options['form_type'];
        $this->actions = new ResourceActionData($options, $this);
    }

    /**
     * @param ApiManager $manager
     */
    public function setManager(ApiManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Return the entity
     *
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entity;
    }

    /**
     * Return the resource name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFilterClass()
    {
        return $this->filterClass;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @return bool
     */
    public function hasPagination()
    {
        return $this->paginate;
    }

    /**
     * @return ResourceActionData
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return $this->manager->getBundlePrefix();
    }

}