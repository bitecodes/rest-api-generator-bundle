<?php

namespace Fludio\ApiAdminBundle\Tests\DependencyInjection;

use Doctrine\ORM\Tools\SchemaTool;
use Fludio\ApiAdminBundle\Resource\Resource;
use Fludio\ApiAdminBundle\Resource\ResourceActionData;
use Fludio\ApiAdminBundle\Tests\Dummy\app\AppKernel;
use Fludio\ApiAdminBundle\Tests\Dummy\TestEntity\Post;
use Symfony\Component\HttpKernel\Kernel;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kernel
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();
    }

    /** @test */
    public function bla()
    {
        $manager = $this->kernel->getContainer()->get('fludio.api_admin.endpoint_manager');
        $config = $manager->getConfigurationForEntity(Post::class);

        $this->assertInstanceOf(Resource::class, $config);
        $this->assertEquals('/my_post', $config->getActions()->getUrl(ResourceActionData::ACTION_INDEX));
        $this->assertCount(7, $config->getActions()->getAvailableActions());
        $this->assertTrue(in_array(ResourceActionData::ACTION_INDEX, $config->getActions()->getAvailableActions()));
        $this->assertTrue(in_array(ResourceActionData::ACTION_SHOW, $config->getActions()->getAvailableActions()));
    }
}