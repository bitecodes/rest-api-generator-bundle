<?php

namespace Fludio\RestApiGeneratorBundle\Subscriber;

use Fludio\RestApiGeneratorBundle\Exception\ApiProblem;
use Fludio\RestApiGeneratorBundle\Exception\ApiProblemException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private $debug;

    public function __construct($debug)
    {
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
        // only reply to /api URLs
//        if (strpos($event->getRequest()->headers->get('Content-Type'), 'application/json') !== 0) {
//            var_dump(get_class($event->getException()));
//            return;
//        }

        $e = $event->getException();

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
