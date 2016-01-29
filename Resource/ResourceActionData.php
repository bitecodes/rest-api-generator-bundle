<?php

namespace Fludio\RestApiGeneratorBundle\Resource;

class ResourceActionData
{
    const ACTION_INDEX = 'index';
    const ACTION_SHOW = 'show';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_BATCH_UPDATE = 'batch_update';
    const ACTION_DELETE = 'delete';
    const ACTION_BATCH_DELETE = 'batch_delete';

    /**
     * @var array
     */
    protected $availableActions;
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @param array $options
     * @param Resource $resource
     */
    public function __construct(array $options, Resource $resource)
    {
        $this->availableActions = ResourceOptions::getAvailableActions($options);
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function getAvailableActions()
    {
        return $this->availableActions;
    }

    /**
     * Return the url for a given action
     *
     * @param $action
     * @return string
     * @throws \Exception
     */
    public function getUrl($action)
    {
        switch ($action) {
            case self::ACTION_INDEX:
            case self::ACTION_CREATE:
            case self::ACTION_BATCH_UPDATE:
            case self::ACTION_BATCH_DELETE:
                $url = $this->getCollectionUrl();
                break;
            case self::ACTION_SHOW:
            case self::ACTION_UPDATE:
            case self::ACTION_DELETE:
                $url = $this->getElementUrl();
                break;
            default:
                throw new \Exception(sprintf('Invalid action \'%s\'', $action));
                break;
        }

        return $url;
    }

    /**
     * Return collection url for an entity
     *
     * @return string
     */
    protected function getCollectionUrl()
    {
        return '/' . $this->resource->getName();
    }

    /**
     * Return url for a single entity
     *
     * @return string
     */
    protected function getElementUrl()
    {
        return $this->getCollectionUrl() . '/{id}';
    }

    /**
     * Return the base url for this entity
     *
     * @return string
     */
    public function getResourceBaseUrl()
    {
        return $this->getCollectionUrl();
    }

    /**
     * Return string of the controller service method for an action
     *
     * @param $action
     * @return string
     */
    public function getControllerAction($action)
    {
        return $this->resource->getServices()->getControllerServiceName() . ':' . $action . 'Action';
    }

    /**
     * Get the name of a route action
     *
     * @param $action
     * @return string
     */
    public function getRouteName($action)
    {
        return $this->resource->getBundlePrefix() . '.' . $action . '.' . $this->resource->getName();
    }
}