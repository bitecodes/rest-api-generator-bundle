<?php

namespace Fludio\ApiAdminBundle\RouteLoader;

use Fludio\ApiAdminBundle\Configuration\Configuration;
use Fludio\ApiAdminBundle\Configuration\Convention;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
    private $routes = [
        Configuration::ROUTE_INDEX => ['GET'],
        Configuration::ROUTE_SHOW => ['GET'],
        Configuration::ROUTE_CREATE => ['POST'],
        Configuration::ROUTE_UPDATE => ['PUT', 'PATCH'],
        Configuration::ROUTE_BATCH_UPDATE => ['PUT', 'PATCH'],
        Configuration::ROUTE_DELETE => ['DELETE'],
        Configuration::ROUTE_BATCH_DELETE => ['DELETE'],
    ];

    private $loaded;
    /**
     * @var array
     */
    private $entites;
    /**
     * @var Convention
     */
    private $convention;

    public function __construct(array $entites, Convention $convention)
    {
        $this->entites = $entites;
        $this->convention = $convention;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->entites as $entity) {
            $apiConfig = new Configuration($entity, $this->convention);

            $this->addRoute($apiConfig, $routes);
        }

        $this->loaded = true;

        return $routes;
    }


    public function supports($resource, $type = null)
    {
        return 'api_crud' === $type;
    }

    /**
     * @param $apiConfig
     * @param $routes
     */
    private function addRoute($apiConfig, $routes)
    {
        foreach ($this->routes as $routeIdentifier => $methods) {
            $route = new Route($apiConfig->getUrl($routeIdentifier));
            $route
                ->setDefaults(['_controller' => $apiConfig->getControllerAction($routeIdentifier)])
                ->setMethods($methods);

            $routes->add($apiConfig->getRouteName($routeIdentifier), $route);
        }
    }

}