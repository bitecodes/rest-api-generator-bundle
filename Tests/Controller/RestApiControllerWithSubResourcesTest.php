<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

class RestApiControllerWithSubResourcesTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithSubResources', true);
        $kernel->setConfigFile('config_sub_resources.yml');
        return $kernel;
    }

    public function setUp()
    {
        parent::setUp();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    /** @test */
    public function it_adds_sub_resources()
    {
        $this->factory->create(Category::class, ['name' => 'coding']);

        $this
            ->get('/categories/1/posts')
            ->seeJsonResponse()
            ->seeStatusCode(200);
    }

    /** @test */
    public function it_does_not_add_sub_resource_as_main_resource()
    {
        $this
            ->get('/posts')
            ->seeJsonResponse()
            ->seeStatusCode(404);
    }
}