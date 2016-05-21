<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Controller\RestApiController;
use BiteCodes\RestApiGeneratorBundle\Util\ResourceNamesFromRouteParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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
            KernelEvents::CONTROLLER => ['resolveParentResources', 128]
        ];
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function resolveParentResources(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController()[0];

        if (!$controller instanceof RestApiController) {
            return;
        }


        if ($resource = $this->getApiResource($request)) {
            $controller->getHandler()->setApiResource($resource);
            $parents = $this->getParentResources($resource, $request);

            $request->attributes->set('parentResources', $parents);
            $controller->getHandler()->setParentResources($parents);
        }
    }

    /**
     * @param Request $request
     * @return ApiResource|bool
     */
    protected function getApiResource(Request $request)
    {
        $apiResourceName = $request->attributes->get('_api_resource');

        return $this->manager->getResource($apiResourceName);
    }

    /**
     * @param ApiResource $apiResource
     * @param Request $request
     * @return ApiResource[]
     */
    protected function getParentResources(ApiResource $apiResource, Request $request)
    {
        $parents = [];

        if ($parentResource = $apiResource->getParentResource()) {
            $id = $request->attributes->get($parentResource->getRoutePlaceholder());
            $parentResource->setIdentifierValue($id);
            $parents[] = $parentResource;
            foreach ($this->getParentResources($parentResource, $request) as $parentsParent) {
                $id = $request->attributes->get($parentsParent->getRoutePlaceholder());
                $parentsParent->setIdentifierValue($id);
                $parents[] = $parentsParent;
            }
        }

        return $parents;
    }
}
