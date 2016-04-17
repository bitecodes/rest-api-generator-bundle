<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Controller\RestApiController;
use BiteCodes\RestApiGeneratorBundle\Util\ResourceNamesFromRouteParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;

class NestedResourceSubscriber implements EventSubscriberInterface
{
    /**
     * @var ApiManager
     */
    private $manager;
    /**
     * @var Router
     */
    private $router;

    public function __construct(ApiManager $manager, Router $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'resolveParentResources'
        ];
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function resolveParentResources(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];

        if (!$controller instanceof RestApiController) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        $routeName = $this->router->match($path)['_route'];
        $bundlePrefix = $this->manager->getBundlePrefix();

        $subResources = ResourceNamesFromRouteParser::getSubResourceNames($routeName, $bundlePrefix);

        $parentResources = [];

        foreach ($subResources as $subResourceName) {
            $subResource = $this->manager->getResource($subResourceName);
            $id = $request->attributes->get($subResource->getRoutePlaceholder());
            $parentResources[$id] = $subResource;
        }

        $request->attributes->set('parentResources', $parentResources);
        $controller->getHandler()->setParentResources($parentResources);
    }

}