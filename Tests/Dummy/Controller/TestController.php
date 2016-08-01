<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Dummy\Controller;

use BiteCodes\RestApiGeneratorBundle\Api\Response\ApiSerialization;
use Symfony\Component\Routing\Annotation\Route;

class TestController implements ApiSerialization
{
    /**
     * @Route("/test", name="test")
     *
     * @return array
     */
    public function someAction()
    {
        return [
            'some' => true
        ];
    }
}