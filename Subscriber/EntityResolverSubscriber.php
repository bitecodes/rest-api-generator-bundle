<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiProblem;
use BiteCodes\RestApiGeneratorBundle\Controller\RestApiController;
use BiteCodes\RestApiGeneratorBundle\Exception\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EntityResolverSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'resolveEntity'
        ];
    }

    public function resolveEntity(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];

        if (!$controller instanceof RestApiController) {
            return;
        }

        $request = $event->getRequest();

        $controllerName = $request->get('_controller');

        if (strpos($controllerName, ':showAction') > 0
            || strpos($controllerName, ':updateAction') > 0
            || strpos($controllerName, ':deleteAction') > 0
        ) {
            $entity = $this->getEntityOrThrowException($request, $controller);

            $request->attributes->add(['entity' => $entity]);
        }
    }

    /**
     * @param Request $request
     * @return null|object
     */
    protected function getEntityOrThrowException(Request $request, RestApiController $controller)
    {
        $id = $this->getId($request);

        if (null === $entity = $controller->getHandler()->get($id)) {
            $problem = new ApiProblem(
                404,
                ApiProblem::TYPE_ENTITY_NOT_FOUND
            );
            throw new ApiProblemException($problem);
        }

        return $entity;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    protected function getId(Request $request)
    {
        $identifier = $request->get('_identifier');
        return $request->get($identifier);
    }

}