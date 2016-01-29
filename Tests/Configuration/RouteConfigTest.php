<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Configuration;

use Fludio\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use Fludio\TestBundle\Test\TestCase;

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
            'fludio.rest_api_generator.index.posts',
            'fludio.rest_api_generator.show.posts',
            'fludio.rest_api_generator.create.posts',
        ]);
    }
}
