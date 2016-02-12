<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Api;

use Fludio\RestApiGeneratorBundle\Api\Response\ApiResponse;

class ApiResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function the_returned_data_is_sorted()
    {
        $response = new \Fludio\RestApiGeneratorBundle\Api\Response\ApiResponse(200, ['my_stuff' => 'comes here']);
        $response->set('meta', 'will be here');
        $response->set('additional_data', 'will be here');

        $data = $response->toArray();
        $keys = array_keys($data);

        $this->assertEquals('status', $keys[0]);
        $this->assertEquals('type', $keys[1]);
        $this->assertEquals('data', $keys[2]);
        $this->assertEquals('additional_data', $keys[3]);
        $this->assertEquals('meta', $keys[4]);
    }
}
