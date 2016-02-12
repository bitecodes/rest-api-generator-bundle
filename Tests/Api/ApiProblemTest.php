<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Api;

use Fludio\RestApiGeneratorBundle\Api\Response\ApiProblem;

class ApiProblemTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_sorts_the_response_data()
    {
        $response = new \Fludio\RestApiGeneratorBundle\Api\Response\ApiProblem(404, null, ['my_stuff' => 'comes here']);
        $response->set('meta', 'will be here');
        $response->set('additional_data', 'will be here');

        $data = $response->toArray();
        $keys = array_keys($data);

        $this->assertEquals('status', $keys[0]);
        $this->assertEquals('type', $keys[1]);
        $this->assertEquals('title', $keys[2]);
        $this->assertEquals('data', $keys[3]);
        $this->assertEquals('additional_data', $keys[4]);
        $this->assertEquals('meta', $keys[5]);
    }
}
