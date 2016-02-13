<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Api\Resource;

use Doctrine\Common\Util\Inflector;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Fludio\RestApiGeneratorBundle\Api\Routing\Action\Create;
use Fludio\RestApiGeneratorBundle\Api\Routing\Action\Delete;
use Fludio\RestApiGeneratorBundle\Api\Routing\Action\Index;
use Fludio\RestApiGeneratorBundle\Api\Routing\Action\Show;
use Fludio\RestApiGeneratorBundle\Api\Routing\Action\Update;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Symfony\Component\Routing\Router;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $bundlePrefix = 'fludio.rest_api_generator';
    /**
     * @var ApiResource
     */
    protected $config;

    public function setUp()
    {
        $router = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = new ApiResource(Post::class);
        $this->config->addAction(new Index($router));
        $this->config->addAction(new Create($router));
        $this->config->addAction(new Show($router));
        $this->config->addAction(new Update($router));
        $this->config->addAction(new Delete($router));

        $manager = new ApiManager();
        $manager->addResource($this->config);
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
     * @param $actionClass
     */
    public function it_returns_the_correct_route_names($actionClass)
    {
        $refl = new \ReflectionClass($actionClass);
        $name = Inflector::tableize($refl->getShortName());

        $this->assertEquals($this->bundlePrefix . '.posts.' . $name, $this->config->getActions()->get($actionClass)->getRouteName());
    }

    /** @test */
    public function it_returns_the_entity_namespace()
    {
        $this->assertEquals(Post::class, $this->config->getEntityClass());
    }

    /**
     *
     * @test
     * @dataProvider routes
     *
     * @param $actionClass
     * @param $expectedUrl
     * @throws \Exception
     */
    public function it_returns_the_route_urls($actionClass, $expectedUrl)
    {
        $this->assertEquals($expectedUrl, $this->config->getActions()->get($actionClass)->getUrlSchema());
    }

    /** @test */
    public function it_returns_the_base_url_for_this_entity()
    {
        $this->assertEquals('/posts', $this->config->getResourceBaseUrl());
    }

    /**
     * //     * @test
     * @dataProvider routes
     *
     * @param $routeType
     * @param $expectedUrl
     * @param $expectedControllerAction
     */
    public function it_returns_the_controller_action_names($routeType, $expectedUrl, $expectedControllerAction)
    {
        $this->assertEquals($expectedControllerAction, $this->config->getActions()->get($routeType)->getControllerAction());
    }

    /** @test */
    public function it_returns_the_controller_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.controller.posts', $this->config->getControllerServiceName());
    }

    /** @test */
    public function it_returns_the_entity_handler_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.entity_handler.posts', $this->config->getEntityHandlerServiceName());
    }

    /** @test */
    public function it_returns_the_form_handler_service_name()
    {
        $this->assertEquals($this->bundlePrefix . '.form_handler.posts', $this->config->getFormHandlerServiceName());
    }

    /**
     * @return array
     */
    public function routes()
    {
        return [
            [Index::class, '/posts', $this->bundlePrefix . '.controller.posts:indexAction'],
            [Show::class, '/posts/{id}', $this->bundlePrefix . '.controller.posts:showAction'],
            [Create::class, '/posts', $this->bundlePrefix . '.controller.posts:createAction'],
            [Update::class, '/posts/{id}', $this->bundlePrefix . '.controller.posts:updateAction'],
            [Delete::class, '/posts/{id}', $this->bundlePrefix . '.controller.posts:deleteAction'],
        ];
    }
}
