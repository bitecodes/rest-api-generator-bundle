<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Configuration;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\TestBundle\Test\TestCase;

class RouteConfigTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRouteConfigTest', false);
        $kernel->setConfigFile('config_routes.yml');
        return $kernel;
    }

    /** @test */
    public function it_returns_only_the_specified_routes_are_registered()
    {
        $router = $this->getContainer()->get('router');

        $routes = $router->getRouteCollection()->all();

        $this->assertCount(3, $routes);

        $routeNames = [];

        foreach ($routes as $name => $route) {
            $routeNames[] = $name;
        }

        $this->assertEquals($routeNames, [
            'bite_codes.rest_api_generator.posts.index',
            'bite_codes.rest_api_generator.posts.show',
            'bite_codes.rest_api_generator.posts.create',
        ]);
    }
}
