<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Fludio\TestBundle\Test\TestCase;

class RestApiControllerWithPaginationTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithPagination', false);
        $kernel->setConfigFile('config_pagination.yml');
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
    public function it_paginates_posts()
    {
        $this->factory->create(Post::class, ['title' => 'Post 1', 'content' => 'My post content']);
        $this->factory->create(Post::class, ['title' => 'Post 2', 'content' => 'Something else']);
        $this->factory->create(Post::class, ['title' => 'Post 3', 'content' => 'Even more']);
        $this->factory->create(Post::class, ['title' => 'Post 4', 'content' => 'Last one']);

        $url = $this->generateUrl('fludio.rest_api_generator.index.posts', ['offset' => 1, 'limit' => 2]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeNotInJson(['title' => 'Post 1'])
            ->seeInJson(['title' => 'Post 2'])
            ->seeInJson(['title' => 'Post 3'])
            ->seeNotInJson(['title' => 'Post 4']);
    }
}
