<?php

namespace Fludio\RestApiGeneratorBundle\Subscriber;

use Fludio\RestApiGeneratorBundle\Api\ApiResponse;
use Fludio\RestApiGeneratorBundle\Controller\RestApiController;
use Fludio\RestApiGeneratorBundle\Services\MetadataStorage\ResponseData;
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
     * @var ResponseData
     */
    private $data;

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
     * @param ResponseData $data
     */
    public function __construct(Serializer $serializer, ResponseData $data)
    {
        $this->serializer = $serializer;
        $this->data = $data;
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
            $data = array_merge($apiResponse->toArray(), $this->data->all());

            $data = array_filter($data);

            $json = $this->serializer->serialize($data, 'json');
            $response = new Response($json, 200, [
                'Content-Type' => 'application/json'
            ]);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}