<?php

namespace Fludio\RestApiGeneratorBundle\Listener;

use Fludio\RestApiGeneratorBundle\Api\ApiProblem;
use Fludio\RestApiGeneratorBundle\Exception\ApiProblemException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class JsonContentListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                $this->validateJsonContent();
            }
            $request->request->replace(is_array($data) ? $data : []);
        }
    }

    protected function validateJsonContent()
    {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
        }

        if (!empty($error)) {
            $problem = new ApiProblem(
                400,
                ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
            );
            $problem->set('error', $error);

            throw new ApiProblemException($problem);
        }
    }
}