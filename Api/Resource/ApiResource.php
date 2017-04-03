<?php

namespace BiteCodes\RestApiGeneratorBundle\Api\Resource;

use BiteCodes\RestApiGeneratorBundle\Api\Actions\Action;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\ActionList;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\ConfigurationProcessor;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\Traits\ServiceNames;
use BiteCodes\RestApiGeneratorBundle\Form\DynamicFormType;

class ApiResource
{
    use ServiceNames;

    const MAIN_RESOURCE = 'main';
    const SUB_RESOURCE = 'sub';

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
     * The config name
     *
     * @var string
     */
    protected $configName;
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
     * @var integer
     */
    protected $paginationLimit;
    /**
     * @var string
     */
    protected $identifier;
    /**
     * @var mixed
     */
    protected $identifierValue;
    /**
     * @var string
     */
    private $entity;
    /**
     * @var Action[]|ActionList
     */
    protected $actions;
    /**
     * @var ServiceNames
     */
    protected $services;
    /**
     * @var ApiResource|null
     */
    protected $parentResource;
    /**
     * @var ApiResource[]
     */
    protected $subResources = [];
    /**
     * @var string
     */
    protected $type = self::MAIN_RESOURCE;
    /**
     * @var string
     */
    protected $assocParent;
    /**
     * @var string
     */
    protected $assocSubResource;
    /**
     * @var string
     */
    protected $prefix;

    public function __construct($resourceName, array $options = [])
    {
        $this->actions = new ActionList();
        $this->name = $resourceName;
        $this->entity = $options['entity'];
        $this->filterClass = $options['filter'];
        $this->paginate = $options['pagination']['enabled'];
        $this->paginationLimit = $options['pagination']['limit'];
        $this->formTypeClass = $options['form_type'];
        $this->identifier = $options['identifier'];
        $this->prefix = $options['prefix'];
    }

    /**
     * @return string
     */
    public function getResourceCollectionUrl()
    {
        return $this->prefix . '/' . $this->getConfigName();
    }

    /**
     * @return string
     */
    public function getResourceSingleElementUrl()
    {
        $parent = $this->parentResource
            ? $this->parentResource->getResourceSingleElementUrl()
            : '';

        return $parent . $this->getResourceCollectionUrl() . '/{' . $this->getRoutePlaceholder() . '}';
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
     * @return ActionList|Action[]
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
     * Set the resource name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * @param string $configName
     */
    public function setConfigName($configName)
    {
        $this->configName = $configName;
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
     * @return mixed
     */
    public function getIdentifierValue()
    {
        return $this->identifierValue;
    }

    /**
     * @param mixed $identifierValue
     */
    public function setIdentifierValue($identifierValue)
    {
        $this->identifierValue = $identifierValue;
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
     * @return int
     */
    public function getPaginationLimit()
    {
        return $this->paginationLimit;
    }

    /**
     * @return string
     */
    public function getBundlePrefix()
    {
        return $this->manager->getBundlePrefix();
    }

    /**
     * @return bool
     */
    public function hasParentResource()
    {
        return !!$this->parentResource;
    }

    /**
     * @return ApiResource|null
     */
    public function getParentResource()
    {
        return $this->parentResource;
    }

    /**
     * @param ApiResource $parentResource
     */
    public function setParentResource(ApiResource $parentResource)
    {
        $this->parentResource = $parentResource;
        $parentResource->addSubResource($this);
    }

    /**
     * @return ApiResource[]
     */
    public function getSubResources()
    {
        return $this->subResources;
    }

    /**
     * @param ApiResource $subResource
     */
    public function addSubResource(ApiResource $subResource)
    {
        $this->subResources[$subResource->getConfigName()] = $subResource;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        if (!in_array($type, [self::MAIN_RESOURCE, self::SUB_RESOURCE])) {
            throw new \InvalidArgumentException("$type is not a valid resource type");
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isMainResource()
    {
        return $this->type === self::MAIN_RESOURCE;
    }

    /**
     * @return bool
     */
    public function isSubResource()
    {
        return $this->type === self::SUB_RESOURCE;
    }

    /**
     * @return string
     */
    public function getAssocParent()
    {
        return $this->assocParent;
    }

    /**
     * @param string $assocParent
     */
    public function setAssocParent($assocParent)
    {
        $this->assocParent = $assocParent;
    }

    /**
     * @return string
     */
    public function getAssocSubResource()
    {
        return $this->assocSubResource;
    }

    /**
     * @param string $assocSubResource
     */
    public function setAssocSubResource($assocSubResource)
    {
        $this->assocSubResource = $assocSubResource;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
