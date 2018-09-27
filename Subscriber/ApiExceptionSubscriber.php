<?php

namespace BiteCodes\RestApiGeneratorBundle\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiProblem;
use BiteCodes\RestApiGeneratorBundle\Controller\RestApiController;
use BiteCodes\RestApiGeneratorBundle\Exception\ApiProblemException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var boolean
     */
    private $debug;

    public function __construct(ContainerInterface $container, $debug)
    {
        $this->container = $container;
        $this->debug = $debug;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        if(!$e instanceof ApiProblemException) {
            $controller = $event->getRequest()->attributes->get('_controller', false);

            if(!$controller) {
                return;
            }

            $controller = substr($controller, 0, strpos($controller, ':'));

            if(!class_exists($controller)) {
                if(!$this->container->has($controller)) {
                    return;
                }

                $controller = $this->container->get($controller);
            }

            if(!$controller instanceof RestApiController) {
                return;
            }
        }

        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        // allow 500 errors to be thrown
        if ($this->debug && $statusCode >= 500) {
            return;
        }

        if ($e instanceof ApiProblemException) {
            $apiProblem = $e->getApiProblem();
        } else {

            $apiProblem = new ApiProblem($statusCode);

            /*
             * If it's an HttpException message (e.g. for 404, 403),
             * we'll say as a rule that the exception message is safe
             * for the client. Otherwise, it could be some sensitive
             * low-level exception, which should *not* be exposed
             */
            if ($e instanceof HttpExceptionInterface) {
                $apiProblem->set('detail', $e->getMessage());
            }
        }

        $data = $apiProblem->toArray();

        $response = new JsonResponse(
            $data,
            $apiProblem->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }
}
