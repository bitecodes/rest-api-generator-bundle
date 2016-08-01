<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiHelper;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiResponse;
use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiSerialization;
use BiteCodes\RestApiGeneratorBundle\Services\MetadataStorage\ResponseData;
use JMS\Serializer\SerializationContext;
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
     * @var ApiManager
     */
    private $manager;

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
    public function __construct(Serializer $serializer, ResponseData $data, ApiManager $manager)
    {
        $this->serializer = $serializer;
        $this->data = $data;
        $this->manager = $manager;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function checkForControllerType(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];
        $this->isRestController = $controller instanceof ApiSerialization;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function serializeResponse(GetResponseForControllerResultEvent $event)
    {

        if ($this->isRestController) {
            $apiResourceName = $event->getRequest()->attributes->get('_api_resource');
            $controllerName = $event->getRequest()->attributes->get('_controller');

            $apiResource = $this->manager->getResource($apiResourceName);
            $action = $apiResource->getAction(ApiHelper::getActionClassFromControllerName($controllerName));

            $data = $event->getControllerResult();

            $apiResponse = new ApiResponse(200, $data);

            $data = array_merge($apiResponse->toArray(), $this->data->all());
            $data = array_filter($data);

            if (!isset($data['data'])) {
                $data['data'] = [];
            }

            $context = new SerializationContext();
            $context->setSerializeNull(true);
            $context->enableMaxDepthChecks();
            $context->setGroups($action->getSerializationGroups());

            $json = $this->serializer->serialize($data, 'json', $context);
            $response = new Response($json, 200, [
                'Content-Type' => 'application/json'
            ]);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}