<?php

namespace Fludio\RestApiGeneratorBundle\Tests\DependencyInjection;

use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Fludio\RestApiGeneratorBundle\Resource\ResourceActionData;
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
        $manager = $this->kernel->getContainer()->get('fludio.rest_api_generator.endpoint_manager');
        $config = $manager->getConfigurationForEntity(Post::class);

        $this->assertInstanceOf(Resource::class, $config);
        $this->assertEquals('/posts', $config->getActions()->getUrl(ResourceActionData::ACTION_INDEX));
        $this->assertCount(7, $config->getActions()->getAvailableActions());
        $this->assertTrue(in_array(ResourceActionData::ACTION_INDEX, $config->getActions()->getAvailableActions()));
        $this->assertTrue(in_array(ResourceActionData::ACTION_SHOW, $config->getActions()->getAvailableActions()));
    }
}