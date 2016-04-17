<?php

namespace BiteCodes\RestApiGeneratorBundle\RouteLoader;

use BiteCodes\RestApiGeneratorBundle\Api\Actions\Action;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Index;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Resource\Convention;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    private $loaded;
    /**
     * @var array
     */
    private $manager;

    public function __construct(ApiManager $manager)
    {
        $this->manager = $manager;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "api_crud" loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->manager->getResources() as $entity => $entityEndpointConfiguration) {
            $this->addRoute($entityEndpointConfiguration, $routes);
        }

        $this->loaded = true;

        return $routes;
    }


    public function supports($resource, $type = null)
    {
        return 'api_crud' === $type;
    }

    /**
     * @param ApiResource $apiResource
     * @param RouteCollection $routes
     * @param ApiResource $parentResource
     */
    private function addRoute(ApiResource $apiResource, RouteCollection $routes, $parentResource = null)
    {
        foreach ($apiResource->getActions() as $action) {

            if (!$parentResource && !$apiResource->isMainResource()) {
                continue;
            }

            $route = new Route($this->getUrl($action, $parentResource));
            $route
                ->setDefault('_controller', $action->getControllerAction())
                ->setDefault('_entity', $apiResource->getEntityClass())
                ->setDefault('_roles', $action->getRoles())
                ->setDefault('_identifier', $apiResource->getIdentifier())
                ->setMethods($action->getMethods());

            if ($action instanceof Index) {
                $route->setDefault('_indexGetterMethod', $action->getResourceGetterMethod());
            }

            $routes->add($action->getRouteName($parentResource), $route);
        }


        foreach ($apiResource->getSubResources() as $subResourceName) {
            $subResource = $this->manager->getResource($subResourceName);
            $this->addRoute($subResource, $routes, $apiResource);
        }
    }

    /**
     * @param Action $action
     * @param ApiResource $parentResource
     * @return mixed
     */
    private function getUrl(Action $action, ApiResource $parentResource = null)
    {
        $parent = $parentResource ? $parentResource->getResourceSingleElementUrl() : '';

        return $parent . $action->getUrlSchema();
    }
}
