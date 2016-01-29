<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Configuration;

use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $bundlePrefix = 'fludio.rest_api_generator';
    /**
     * @var Resource
     */
    protected $config;

    public function setUp()
    {
        $this->config = new Resource(Post::class);

        $manager = new ResourceManager();
        $manager->addConfiguration($this->config);
    }

    /** @test */
    public function it_returns_the_resource_name()
    {
        $this->assertEquals('posts', $this->config->getName());
    }

    /**
     * @test
     * @dataProvider routes
     *
     * @param $routeType
     */
    public function it_returns_the_correct_route_names($routeType)
    {
        $this->assertEquals($this->bundlePrefix . '.' . $routeType . '.posts', $this->config->getActions()->getRouteName($routeType));
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
        $this->assertEquals($expectedUrl, $this->config->getActions()->getUrl($routeType));
    }

    /** @test */
    public function it_returns_the_base_url_for_this_entity()
    {
        $this->assertEquals('/posts', $this->config->getActions()->getResourceBaseUrl());
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
        $this->assertEquals($expectedControllerAction, $this->config->getActions()->getControllerAction($routeType));
    }

    /** @test */
    public function it_returns_the_controller_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.controller.posts', $this->config->getServices()->getControllerServiceName());
    }

    /** @test */
    public function it_returns_the_repository_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.repositories.posts', $this->config->getServices()->getRepositoryServiceName());
    }

    /** @test */
    public function it_returns_the_entity_handler_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.entity_handler.posts', $this->config->getServices()->getEntityHandlerServiceName());
    }

    /** @test */
    public function it_returns_the_form_handler_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.form_handler.posts', $this->config->getServices()->getFormHandlerServiceName());
    }

    /**
     * @return array
     */
    public function routes()
    {
        return [
            [ResourceActionData::ACTION_INDEX, '/posts', $this->bundlePrefix . '.controller.posts:indexAction'],
            [ResourceActionData::ACTION_SHOW, '/posts/{id}', $this->bundlePrefix . '.controller.posts:showAction'],
            [ResourceActionData::ACTION_CREATE, '/posts', $this->bundlePrefix . '.controller.posts:createAction'],
            [ResourceActionData::ACTION_UPDATE, '/posts/{id}', $this->bundlePrefix . '.controller.posts:updateAction'],
            [ResourceActionData::ACTION_DELETE, '/posts/{id}', $this->bundlePrefix . '.controller.posts:deleteAction'],
        ];
    }
}
