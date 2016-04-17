<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
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
    public function it_does_not_add_sub_resource_as_main_resource_if_configured()
    {
        $this
            ->get('/posts')
            ->seeJsonResponse()
            ->seeStatusCode(404);
    }

    /** @test */
    public function it_returns_sub_resources_for_a_parent_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Post::class, [
            'title' => 'Yoga',
            'content' => 'yoga content'
        ]);

        $this
            ->get('/categories/1/posts')
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonHas('data', 1)
            ->seeInJson(['title' => 'Learn to code']);
    }

    /** @test */
    public function it_returns_a_single_elment_for_a_parent_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Post::class, [
            'title' => 'Yoga',
            'content' => 'yoga content'
        ]);

        $this
            ->get('/categories/1/posts/1')
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Learn to code'])
            ->seeNotInJson(['title' => 'Yoga']);
    }

    /** @test */
    public function it_returns_404_if_single_element_is_not_associated_to_parent_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Post::class, [
            'title' => 'Yoga',
            'content' => 'yoga content'
        ]);

        $this
            ->get('/categories/1/posts/2')
            ->seeJsonResponse()
            ->seeJsonHas('data', 0)
            ->seeNotInJson(['title' => 'Yoga'])
            ->seeStatusCode(404);
    }

    /** @test */
    public function it_adds_a_subresource_and_associates_it_to_the_parent_resource()
    {
        $this->factory->create(Category::class, ['name' => 'coding']);

        $params = [
            'title' => 'Learn programming',
            'content' => 'the text'
        ];

        $this
            ->post('/categories/1/posts', $params)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson($params)
            ->seeInJson(['name' => 'coding'])
            ->seeInDatabase(Post::class, $params);
    }

    /** @test */
    public function it_returns_404_if_parent_resource_does_not_exist()
    {
        $params = [
            'title' => 'Learn programming',
            'content' => 'the text'
        ];

        $this
            ->post('/categories/1/posts', $params)
            ->seeJsonResponse()
            ->seeStatusCode(422)
            ->seeNotInDatabase(Post::class, []);
    }
}