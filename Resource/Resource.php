<?php

namespace Fludio\RestApiGeneratorBundle\Resource;

use Doctrine\Common\Inflector\Inflector;
use Fludio\DoctrineFilter\FilterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Resource
{
    /**
     * @var ResourceManager
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
     * @var ResourceServiceData
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
        $this->services = new ResourceServiceData($options, $this);
    }

    /**
     * @param ResourceManager $manager
     */
    public function setManager(ResourceManager $manager)
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
     * @return ResourceServiceData
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return $this->manager->getBundlePrefix();
    }

}