<?php

namespace Fludio\ApiAdminBundle\Tests\Configuration;

use Fludio\ApiAdminBundle\Configuration\Configuration;
use Fludio\ApiAdminBundle\Configuration\Convention;
use Fludio\ApiAdminBundle\Tests\Dummy\Entity\Post;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $bundlePrefix = 'fludio.api_admin';
    /**
     * @var Configuration
     */
    protected $config;

    public function setUp()
    {
        $convention = new Convention([
            'bundlePrefix' => $this->bundlePrefix
        ]);

        $this->config = new Configuration(Post::class, $convention);
    }

    /** @test */
    public function it_returns_the_resource_name()
    {
        $this->assertEquals('post', $this->config->getResourceName());
    }

    /**
     * @test
     * @dataProvider routes
     *
     * @param $routeType
     */
    public function it_returns_the_correct_route_names($routeType)
    {
        $this->assertEquals($this->bundlePrefix . '.' . $routeType . '.post', $this->config->getRouteName($routeType));
    }

    /** @test */
    public function it_returns_the_entity_namespace()
    {
        $this->assertEquals(Post::class, $this->config->getEntityNamespace());
    }

    /**
     *
     * @test
     * @dataProvider routes
     *
     * @param $routeType
     * @param $expectedUrl
     * @throws \Exception
     */
    public function it_returns_the_route_urls($routeType, $expectedUrl)
    {
        $this->assertEquals($expectedUrl, $this->config->getUrl($routeType));
    }

    /** @test */
    public function it_returns_the_base_url_for_this_entity()
    {
        $this->assertEquals('/posts', $this->config->getResourceBaseUrl());
    }

    /**
     * @test
     * @dataProvider routes
     *
     * @param $routeType
     * @param $expectedUrl
     * @param $expectedControllerAction
     */
    public function it_returns_the_controller_action_names($routeType, $expectedUrl, $expectedControllerAction)
    {
        $this->assertEquals($expectedControllerAction, $this->config->getControllerAction($routeType));
    }

    /** @test */
    public function it_returns_the_controller_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.controller.post', $this->config->getControllerServiceName());
    }

    /** @test */
    public function it_returns_the_repository_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.repositories.post', $this->config->getRepositoryServiceName());
    }

    /** @test */
    public function it_returns_the_entity_handler_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.entity_handler.post', $this->config->getEntityHandlerServiceName());
    }

    /** @test */
    public function it_returns_the_form_handler_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.form_handler.post', $this->config->getFormHandlerServiceName());
    }

    /**
     * @return array
     */
    public function routes()
    {
        return [
            [Configuration::ROUTE_INDEX, '/posts', $this->bundlePrefix . '.controller.post:indexAction'],
            [Configuration::ROUTE_SHOW, '/posts/{id}', $this->bundlePrefix . '.controller.post:showAction'],
            [Configuration::ROUTE_CREATE, '/posts', $this->bundlePrefix . '.controller.post:createAction'],
            [Configuration::ROUTE_UPDATE, '/posts/{id}', $this->bundlePrefix . '.controller.post:updateAction'],
            [Configuration::ROUTE_DELETE, '/posts/{id}', $this->bundlePrefix . '.controller.post:deleteAction'],
        ];
    }
}
