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
            $deleteBulk = new Route($apiConfig->getUrl(Configuration::ROUTE_BATCH_DELETE), ['_controller' => $apiConfig->getControllerAction('batch_delete')]);
            $deleteBulk->setMethods(['DELETE']);

            $routes->add($apiConfig->getRouteName(Configuration::ROUTE_INDEX), $index);
            $routes->add($apiConfig->getRouteName(Configuration::ROUTE_SHOW), $show);
            $routes->add($apiConfig->getRouteName(Configuration::ROUTE_CREATE), $create);
            $routes->add($apiConfig->getRouteName(Configuration::ROUTE_UPDATE), $update);
            $routes->add($apiConfig->getRouteName(Configuration::ROUTE_DELETE), $delete);
            $routes->add($apiConfig->getRouteName(Configuration::ROUTE_BATCH_DELETE), $deleteBulk);
        }

        $this->loaded = true;

        return $routes;
    }


    public function supports($resource, $type = null)
    {
        return 'api_crud' === $type;
    }

}