<?php

namespace Fludio\RestApiGeneratorBundle\Api\Resource;

use Fludio\RestApiGeneratorBundle\Api\Actions\Action;
use Fludio\RestApiGeneratorBundle\Api\Actions\ActionList;
use Fludio\RestApiGeneratorBundle\DependencyInjection\ConfigurationProcessor;
use Fludio\RestApiGeneratorBundle\Api\Resource\Traits\ServiceNames;

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

    public function __construct($entity, array $options = [])
    {
        $options = \Fludio\RestApiGeneratorBundle\DependencyInjection\ConfigurationProcessor::resolve($entity, $options);
        $this->actions = new ActionList();
        $this->name = $options['resource_name'];
        $this->entity = $entity;
        $this->filterClass = $options['filter'];
        $this->paginate = $options['paginate'];
        $this->formTypeClass = $options['form_type'];
        $this->identifier = $options['identifier'];
    }

    public function getResourceBaseUrl()
    {
        return '/' . $this->getName();
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
}
