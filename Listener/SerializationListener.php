<?php

namespace Fludio\ApiAdminBundle\Listener;

use Fludio\ApiAdminBundle\Controller\RestApiController;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class SerializationListener
{
    /**
     * @var Serializer
     */
    private $serializer;
    private $isRestController = false;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];
        $this->isRestController = $controller instanceof RestApiController;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if ($this->isRestController) {
            $data = $event->getControllerResult();

            $json = $this->serializer->serialize($data, 'json');
            $response = new Response($json, 200, [
                'Content-Type' => 'application/json'
            ]);

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}