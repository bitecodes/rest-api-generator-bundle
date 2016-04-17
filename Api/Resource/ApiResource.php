<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Resource;

use BiteCodes\RestApiGeneratorBundle\Api\Actions\Action;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\ActionList;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\ConfigurationProcessor;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\Traits\ServiceNames;

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
    protected $identifier;
    /**
     * @var string
     */
    private $entity;
    /**
     * @var Action[]
     */
    protected $actions;
    /**
     * @var ServiceNames
     */
    protected $services;
    /**
     * @var array
     */
    protected $subResources;
    /**
     * @var boolean
     */
    protected $isMainResource;

    public function __construct($resourceName, array $options = [])
    {
        $options = ConfigurationProcessor::resolve($resourceName, $options);
        $this->actions = new ActionList();
        $this->name = $options['resource_name'];
        $this->entity = $options['entity'];
        $this->filterClass = $options['filter'];
        $this->paginate = $options['paginate'];
        $this->formTypeClass = $options['form_type'];
        $this->identifier = $options['identifier'];
        $this->subResources = $options['sub_resources'];
        $this->isMainResource = $options['is_main_resource'];
    }

    /**
     * @return string
     */
    public function getResourceCollectionUrl()
    {
        return '/' . $this->getName();
    }

    /**
     * @return string
     */
    public function getResourceSingleElementUrl()
    {
        return $this->getResourceCollectionUrl() . '/{' . $this->getRoutePlaceholder() . '}';
    }

    /**
     * @param ApiManager $manager
     */
    public function setManager(ApiManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Action $action
     */
    public function addAction(Action $action)
    {
        $this->actions->add($action);
        $action->setApiResource($this);
    }

    /**
     * @return ActionList
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param $actionClass
     * @return Action
     */
    public function getAction($actionClass)
    {
        return $this->actions->get($actionClass);
    }

    /**
     * Return the entity
     *
     * @return mixed
     */
    public function getEntityClass()
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
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getRoutePlaceholder()
    {
        return $this->getName() . ucwords($this->identifier);
    }

    /**
     * @return bool
     */
    public function hasPagination()
    {
        return $this->paginate;
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return $this->manager->getBundlePrefix();
    }

    /**
     * @return array
     */
    public function getSubResources()
    {
        return $this->subResources;
    }

    /**
     * @param $resource
     */
    public function addSubResource($resource)
    {
        $this->subResources[] = $resource;
    }

    /**
     * @return boolean
     */
    public function isMainResource()
    {
        return $this->isMainResource;
    }
}
