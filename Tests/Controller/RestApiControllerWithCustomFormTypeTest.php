<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\DatabaseReset;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

class RestApiControllerWithCustomFormTypeTest extends TestCase
{
    use DatabaseReset;

    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithCustomFormType', true);
        $kernel->setConfigFile('config_custom_form_type.yml');
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
    public function it_uses_a_custom_form_type_to_create_a_resource()
    {
        $params = [
            'title' => 'My post',
            'content' => 'some content'
        ];

        $this
            ->post('/posts', $params)
            ->seeJsonResponse()
            ->seeStatusCode(200);
    }

    /** @test */
    public function it_batch_creates_new_entities_with_custom_form_type()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.create');

        $post1 = $this->factory->values(Post::class, ['title' => 'Post 1', 'content' => 'bla']);
        $post2 = $this->factory->values(Post::class, ['title' => 'Post 2', 'content' => 'bla']);

        $this
            ->post($url, [$post1, $post2], ['HTTP_batch' => true])
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Post 1'])
            ->seeInDatabase(Post::class, ['title' => 'Post 1'])
            ->seeInDatabase(Post::class, ['title' => 'Post 2']);
    }
}