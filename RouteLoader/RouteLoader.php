<?php

namespace Fludio\ApiAdminBundle\RouteLoader;

use Fludio\ApiAdminBundle\Configuration\Configuration;
use Fludio\ApiAdminBundle\Configuration\Convention;
use Fludio\ApiAdminBundle\Util\RouteHelper;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader
{
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

            $index = new Route($apiConfig->getUrl(Configuration::ROUTE_INDEX), ['_controller' => $apiConfig->getControllerAction('index')]);
            $index->setMethods(['GET']);
            $show = new Route($apiConfig->getUrl(Configuration::ROUTE_SHOW), ['_controller' => $apiConfig->getControllerAction('show')]);
            $show->setMethods(['GET']);
            $create = new Route($apiConfig->getUrl(Configuration::ROUTE_CREATE), ['_controller' => $apiConfig->getControllerAction('create')]);
            $create->setMethods(['POST']);
            $update = new Route($apiConfig->getUrl(Configuration::ROUTE_UPDATE), ['_controller' => $apiConfig->getControllerAction('update')]);
            $update->setMethods(['PATCH']);
            $delete = new Route($apiConfig->getUrl(Configuration::ROUTE_DELETE), ['_controller' => $apiConfig->getControllerAction('delete')]);
            $delete->setMethods(['DELETE']);


            $routes->add($apiConfig->getRouteName('index'), $index);
            $routes->add($apiConfig->getRouteName('show'), $show);
            $routes->add($apiConfig->getRouteName('create'), $create);
            $routes->add($apiConfig->getRouteName('update'), $update);
            $routes->add($apiConfig->getRouteName('delete'), $delete);
        }

        $this->loaded = true;

        return $routes;
    }


    public function supports($resource, $type = null)
    {
        return 'api_crud' === $type;
    }

}