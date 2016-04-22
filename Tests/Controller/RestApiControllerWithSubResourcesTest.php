<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Comment;
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
    public function it_returns_422_if_parent_resource_does_not_exist_on_create()
    {
        $params = [
            'title' => 'Learn programming',
            'content' => 'the text'
        ];

        $this
            ->post('/categories/1/posts', $params)
            ->seeJsonResponse()
            // TODO 404 might be better
            ->seeStatusCode(422)
            ->seeNotInDatabase(Post::class, []);
    }

    /** @test */
    public function it_updates_a_sub_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $this
            ->patch('/categories/1/posts/1', ['title' => 'Pro coder'])
            ->seeJsonResponse()
            ->seeInJson(['title' => 'Pro coder'])
            ->seeStatusCode(200);
    }

    /** @test */
    public function it_returns_404_if_parent_resource_can_not_be_found_on_update()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $this
            ->patch('/categories/2/posts/1', ['title' => 'Pro coder'])
            ->seeJsonResponse()
            ->seeInJson(['data' => []])
            ->seeStatusCode(404);
    }

    /** @test */
    public function it_returns_404_if_sub_resource_can_not_be_found_on_update()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $this
            ->patch('/categories/1/posts/2', ['title' => 'Pro coder'])
            ->seeJsonResponse()
            ->seeInJson(['data' => []])
            ->seeStatusCode(404);
    }

    /** @test */
    public function it_deletes_a_sub_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $this
            ->delete('/categories/1/posts/1')
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['data' => []])
            ->seeNotInDatabase(Post::class, []);
    }

    /** @test */
    public function it_returns_404_if_sub_resource_can_not_be_found_on_delete()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $this
            ->delete('/categories/1/posts/2')
            ->seeJsonResponse()
            ->seeStatusCode(404)
            ->seeInJson(['data' => []])
            ->seeInDatabase(Post::class, []);
    }

    /** @test */
    public function it_returns_404_if_parent_resource_can_not_be_found_on_delete()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $this
            ->delete('/categories/2/posts/1')
            ->seeJsonResponse()
            ->seeStatusCode(404)
            ->seeInJson(['data' => []])
            ->seeInDatabase(Post::class, []);
    }

    /** @test */
    public function sub_resources_can_have_other_sub_resources()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $post = $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'some comment',
            'post' => $post
        ]);

        $this
            ->get('/categories/1/posts/1/comments')
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonHas('data', 1)
            ->seeInJson(['body' => 'some comment']);
    }

    /** @test */
    public function it_returns_nested_sub_resources_for_a_parent_resources()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $codePost = $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $yogaPost = $this->factory->create(Post::class, [
            'title' => 'Yoga',
            'content' => 'yoga content',
            'category' => $category
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'code comment',
            'post' => $codePost
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'yoga comment',
            'post' => $yogaPost
        ]);

        $this
            ->get('/categories/1/posts/1/comments')
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonHas('data', 1)
            ->seeInJson(['body' => 'code comment'])
            ->seeNotInJson(['body' => 'yoga comment']);
    }

    /** @test */
    public function it_filters_by_all_parent_resources()
    {
        $codingCategory = $this->factory->create(Category::class, ['name' => 'coding']);
        $yogaCategory = $this->factory->create(Category::class, ['name' => 'yoga']);
        $codePost = $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $codingCategory
        ]);
        $yogaPost = $this->factory->create(Post::class, [
            'title' => 'Yoga',
            'content' => 'yoga content',
            'category' => $yogaCategory
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'code comment',
            'post' => $codePost
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'yoga comment',
            'post' => $yogaPost
        ]);

        $this
            ->get('/categories/2/posts/1/comments')
            ->seeJsonResponse()
            ->seeStatusCode(200)// TODO return 404
            ->seeInJson(['data' => []]);
    }

    /** @test */
    public function it_shows_a_single_nested_sub_resources()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $post = $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'first comment',
            'post' => $post
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'second comment',
            'post' => $post
        ]);

        $this
            ->get('/categories/1/posts/1/comments/2')
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['body' => 'second comment'])
            ->seeNotInJson(['body' => 'first comment']);
    }

    /** @test */
    public function it_returns_404_if_nested_sub_resource_if_not_associated_to_parents()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $post = $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'first comment',
            'post' => $post
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'second comment',
        ]);

        $this
            ->get('/categories/1/posts/1/comments/2')
            ->seeJsonResponse()
            ->seeStatusCode(404)
            ->seeInJson(['data' => []]);
    }

    /** @test */
    public function it_adds_a_nested_sub_resource_and_associates_it_to_the_parent_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);

        $params = [
            'body' => 'my comment',
        ];

        $this
            ->post('/categories/1/posts/1/comments', $params)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson($params)
            ->seeInJson(['title' => 'Learn to code'])
            ->seeInDatabase(Comment::class, $params);
    }

    /** @test */
    public function it_updates_a_nested_sub_resource()
    {
        $category = $this->factory->create(Category::class, ['name' => 'coding']);
        $post = $this->factory->create(Post::class, [
            'title' => 'Learn to code',
            'content' => 'some content',
            'category' => $category
        ]);
        $this->factory->create(Comment::class, [
            'body' => 'old comment',
            'post' => $post
        ]);

        $params = [
            'body' => 'updated comment',
        ];

        $this
            ->seeInDatabase(Comment::class, [
                'body' => 'old comment',
                'post' => $post
            ])
            ->patch('/categories/1/posts/1/comments/1', $params)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson($params)
            ->seeInJson(['title' => 'Learn to code'])
            ->seeInDatabase(Comment::class, [
                'body' => 'updated comment',
                'post' => $post
            ]);
    }
}
