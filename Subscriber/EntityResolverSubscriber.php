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
    /**
     * @var ApiProblemException|null
     */
    protected $notFoundException;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['resolveEntity', 16],
                ['throwException', -16]
            ]
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
            $entity = $this->getEntityOrSetException($request, $controller);

            $request->attributes->add(['entity' => $entity]);
        }
    }

    public function throwException(FilterControllerEvent $event)
    {
        if ($this->notFoundException) {
            throw $this->notFoundException;
        }
    }

    /**
     * @param Request $request
     * @param RestApiController $controller
     * @return null|object
     */
    protected function getEntityOrSetException(Request $request, RestApiController $controller)
    {
        $id = $this->getId($request);

        if (null === $entity = $controller->getHandler()->get($id)) {
            $problem = new ApiProblem(
                404,
                ApiProblem::TYPE_ENTITY_NOT_FOUND
            );

            $this->notFoundException = new ApiProblemException($problem);
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
