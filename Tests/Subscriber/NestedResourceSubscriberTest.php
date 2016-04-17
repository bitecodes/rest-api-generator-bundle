<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

class NestedResourceSubscriberTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testNestedResourceSubscriber', true);
        $kernel->setConfigFile('config_sub_resources.yml');
        return $kernel;
    }

    public function setUp()
    {
        parent::setUp();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    /** @test */
    public function it_adds_parents_resources_to_the_request_attributes()
    {
        $this->factory->create(Category::class, ['name' => 'coding']);

        $this->get('/categories/1/posts');

        $request = $this->client->getRequest();

        $this->assertCount(1, $request->attributes->get('parentResources'));
        $this->assertTrue(isset($request->attributes->get('parentResources')[1]));
        $categoryResource = $request->attributes->get('parentResources')[1];
        $this->assertEquals('categories', $categoryResource->getName());
    }
}
