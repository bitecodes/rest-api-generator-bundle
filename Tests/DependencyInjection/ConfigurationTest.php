<?php

namespace Fludio\RestApiGeneratorBundle\Tests\DependencyInjection;

use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Fludio\RestApiGeneratorBundle\Api\Actions\Index;
use Fludio\RestApiGeneratorBundle\Api\Actions\Show;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Symfony\Component\HttpKernel\Kernel;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kernel
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = new AppKernel('testConfiguration', true);
        $this->kernel->setConfigFile('config_simple.yml');
        $this->kernel->boot();
    }

    /** @test */
    public function bla()
    {
        /** @var ApiManager $manager */
        $manager = $this->kernel->getContainer()->get('fludio.rest_api_generator.endpoint_manager');
        $apiResource = $manager->getResourceForEntity(Post::class);

        $this->assertInstanceOf(ApiResource::class, $apiResource);
        $this->assertEquals('/posts', $apiResource->getAction(Index::class)->getUrl());
        $this->assertCount(7, $apiResource->getActions());
        $this->assertInstanceOf(Index::class, $apiResource->getActions()->get(Index::class));
        $this->assertInstanceOf(Show::class, $apiResource->getActions()->get(Show::class));
    }
}