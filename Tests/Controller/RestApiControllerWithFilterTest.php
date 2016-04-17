<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;

class RestApiControllerWithFilterTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithFilter', true);
        $kernel->setConfigFile('config_filter.yml');
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
    public function it_filters_posts()
    {
        $this->factory->create(Post::class, ['title' => 'Post 1', 'content' => 'My post content']);
        $this->factory->create(Post::class, ['title' => 'Post 2', 'content' => 'Something else']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index', ['title' => 'Post 2']);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Post 2'])
            ->seeNotInJson(['title' => 'Post 1']);
    }
}
