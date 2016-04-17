<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;

class RestApiControllerWithNonStandardIdentifierTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithNonStandardIdentifier', false);
        $kernel->setConfigFile('config_identifier.yml');
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
    public function it_returns_an_entity_by_a_non_standard_identifier()
    {
        $this->factory->create(Post::class, ['title' => 'Post_1', 'content' => 'My post content']);
        $this->factory->create(Post::class, ['title' => 'Post_2', 'content' => 'Something else']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.show', ['title' => 'Post_1']);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Post_1']);
    }

    /** @test */
    public function it_updates_an_entity_by_a_non_standard_identifier()
    {
        $this->factory->create(Post::class, ['title' => 'Post_1', 'content' => 'My post content']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.update', ['title' => 'Post_1']);

        $this
            ->patch($url, ['content' => 'Updated content'])
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Post_1'])
            ->seeInJson(['content' => 'Updated content']);
    }

    /** @test */
    public function it_deletes_an_entity_by_a_non_standard_identifier()
    {
        $this->factory->create(Post::class, ['title' => 'Post_1', 'content' => 'My post content']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.delete', ['title' => 'Post_1']);

        $this
            ->delete($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeNotInDatabase(Post::class, []);
    }
}
