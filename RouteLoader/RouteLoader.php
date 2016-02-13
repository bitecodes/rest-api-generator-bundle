<?php

namespace Fludio\RestApiGeneratorBundle\RouteLoader;

use Fludio\RestApiGeneratorBundle\Api\Actions\Index;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Fludio\RestApiGeneratorBundle\Resource\Convention;
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
     */
    private function addRoute(ApiResource $apiResource, RouteCollection $routes)
    {
        foreach ($apiResource->getActions() as $action) {
            $route = new Route($action->getUrlSchema());
            $route
                ->setDefault('_controller', $action->getControllerAction())
                ->setDefault('_entity', $apiResource->getEntityClass())
                ->setDefault('_roles', $action->getRoles())
                ->setDefault('_identifier', $apiResource->getIdentifier())
                ->setMethods($action->getMethods());

            if ($action instanceof Index) {
                $route->setDefault('_indexGetterMethod', $action->getResourceGetterMethod());
            }

            $routes->add($action->getRouteName(), $route);
        }
    }
}
