<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiHelper;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiResponse;
use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiSerialization;
use BiteCodes\RestApiGeneratorBundle\Serialization\FieldsListExclusionStrategy;
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
    private $doSerialize = false;
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
            KernelEvents::VIEW => ['serializeResponse', 128]
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
        $controller = $event->getController();

        if(is_array($controller)) {
            $controller = $controller[0];
        }

        $this->doSerialize = $controller instanceof ApiSerialization;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function serializeResponse(GetResponseForControllerResultEvent $event)
    {

        if ($this->doSerialize) {
            $data = $event->getControllerResult();

            $apiResponse = new ApiResponse(200, $data);

            $data = array_merge($apiResponse->toArray(), $this->data->all());
            $data = array_filter($data);

            if (!isset($data['data'])) {
                $data['data'] = [];
            }

            $context = new SerializationContext();
            $context->setSerializeNull(true);
            if (method_exists($context, 'enableMaxDepthChecks')) {
                $context->enableMaxDepthChecks();
            }

            if ($action = $this->getAction($event)) {
                $context->setGroups($action->getSerializationGroups());
            }

            if ($fields = $event->getRequest()->query->get('fields')) {
                $context->addExclusionStrategy(new FieldsListExclusionStrategy($fields));
            }

            $json = $this->serializer->serialize($data, 'json', $context);
            $response = new Response($json, 200, [
                'Content-Type' => 'application/json'
            ]);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     * @return \BiteCodes\RestApiGeneratorBundle\Api\Actions\Action|bool
     */
    protected function getAction(GetResponseForControllerResultEvent $event)
    {
        $apiResourceName = $event->getRequest()->attributes->get('_api_resource');
        $controllerName = $event->getRequest()->attributes->get('_controller');

        $apiResource = $this->manager->getResource($apiResourceName);

        return $apiResource
            ? $apiResource->getAction(ApiHelper::getActionClassFromControllerName($controllerName))
            : false;
    }
}