<?php

namespace Fludio\RestApiGeneratorBundle\Subscriber;

use Fludio\RestApiGeneratorBundle\Api\ApiResponse;
use Fludio\RestApiGeneratorBundle\Controller\RestApiController;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SerializationSubscriber implements EventSubscriberInterface
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var bool
     */
    private $isRestController = false;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'checkForControllerType',
            KernelEvents::VIEW => 'serializeResponse'
        ];
    }

    /**
     * SerializationSubscriber constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function checkForControllerType(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];
        $this->isRestController = $controller instanceof RestApiController;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function serializeResponse(GetResponseForControllerResultEvent $event)
    {
        if ($this->isRestController) {
            $data = $event->getControllerResult();

            $apiResponse = new ApiResponse(200, $data);

            $json = $this->serializer->serialize($apiResponse->toArray(), 'json');
            $response = new Response($json, 200, [
                'Content-Type' => 'application/json'
            ]);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}