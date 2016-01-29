<?php

namespace Fludio\RestApiGeneratorBundle\RouteLoader;

use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Fludio\RestApiGeneratorBundle\Resource\Convention;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    private $routes = [
        ResourceActionData::ACTION_INDEX => ['GET'],
        ResourceActionData::ACTION_SHOW => ['GET'],
        ResourceActionData::ACTION_CREATE => ['POST'],
        ResourceActionData::ACTION_UPDATE => ['PUT', 'PATCH'],
        ResourceActionData::ACTION_BATCH_UPDATE => ['PUT', 'PATCH'],
        ResourceActionData::ACTION_DELETE => ['DELETE'],
        ResourceActionData::ACTION_BATCH_DELETE => ['DELETE'],
    ];

    private $loaded;
    /**
     * @var array
     */
    private $manager;

    public function __construct(ResourceManager $manager)
    {
        $this->manager = $manager;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->manager->getConfigurations() as $entity => $entityEndpointConfiguration) {
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
     * @param Resource $apiConfig
     * @param $routes
     * @throws \Exception
     */
    private function addRoute(Resource $apiConfig, $routes)
    {
        foreach ($apiConfig->getActions()->getAvailableActions() as $routeIdentifier) {
            $route = new Route($apiConfig->getActions()->getUrl($routeIdentifier));
            $route
                ->setDefault('_controller', $apiConfig->getActions()->getControllerAction($routeIdentifier))
                ->setDefault('_entity', $apiConfig->getEntityNamespace())
                ->setDefault('_roles', $apiConfig->getActions()->getSecurityForAction($routeIdentifier))
                ->setMethods($this->routes[$routeIdentifier]);

            $routes->add($apiConfig->getActions()->getRouteName($routeIdentifier), $route);
        }
    }

}