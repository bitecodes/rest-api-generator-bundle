<?php

namespace Fludio\ApiAdminBundle\Tests\Controller;

use Fludio\TestBundle\Test\DatabaseReset;
use Fludio\TestBundle\Test\TestCase;
use Fludio\ApiAdminBundle\Entity\Post;

class PostControllerTest extends TestCase
{
    use DatabaseReset;

    /** @test */
    public function it_returns_multiple_posts()
    {
        $this->factory->times(2)->create(Post::class, ['title' => 'My Post']);

        $url = $this->generateUrl('fludio.api_admin.index.post');

        $this
            ->get($url, ['HTTP_Accept' => 'application/json'])
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonContains(['title' => 'My Post']);
    }

    /** @test */
    public function it_returns_a_single_post()
    {
        $post = $this->factory->create(Post::class, ['title' => 'My Post']);

        $url = $this->generateUrl('fludio.api_admin.show.post', ['id' => $post->getId()]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonContains(['title' => 'My Post']);
    }

//    /** @test */
//    public function it_returns_400_if_entity_not_found()
//    {
//        $url = $this->generateUrl('api_get_post', ['id' => 1]);
//
//        $this
//            ->get($url)
//            ->seeJsonResponse()
//            ->seeStatusCode(400);
//    }

    /** @test */
    public function it_creates_a_new_post()
    {
        $url = $this->generateUrl('fludio.api_admin.create.post');

        $data = $this->factory->values(Post::class, ['title' => 'My Post']);

        $this
            ->post($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonContains(['title' => 'My Post'])
            ->seeInDatabase(Post::class, ['title' => 'My Post']);
    }

    /** @test */
    public function it_updates_posts_with_put()
    {
        $post = $this->factory->create(Post::class);

        $url = $this->generateUrl('fludio.api_admin.update.post', ['id' => $post->getId()]);

        $data = [
            'title' => $post->getTitle(),
            'content' => 'some_content',
        ];

        $this
            ->put($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonContains(['title' => $post->getTitle()])
            ->seeJsonContains(['content' => 'some_content'])
            ->seeInDatabase(Post::class, $data);
    }

    /** @test */
    public function it_will_not_update_if_put_does_not_provide_all_data()
    {
        $post = $this->factory->create(Post::class);

        $url = $this->generateUrl('fludio.api_admin.update.post', ['id' => $post->getId()]);

        $data = [
            'content' => 'some_content',
        ];

        $this
            ->put($url, $data)
            ->seeStatusCode(500)
            ->seeInDatabase(Post::class, ['title' => $post->getTitle(), 'content' => $post->getContent()]);
    }

    /** @test */
    public function it_updates_posts_with_patch()
    {
        $post = $this->factory->create(Post::class);

        $url = $this->generateUrl('fludio.api_admin.update.post', ['id' => $post->getId()]);

        $data = [
            'content' => 'some_content',
        ];

        $this
            ->patch($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeJsonContains(['title' => $post->getTitle()])
            ->seeJsonContains(['content' => 'some_content'])
            ->seeInDatabase(Post::class, $data);
    }

    /** @test */
    public function it_deletes_posts()
    {
        $post = $this->factory->create(Post::class);

        $url = $this->generateUrl('fludio.api_admin.delete.post', ['id' => $post->getId()]);

        $this
            ->seeInDatabase(Post::class, ['id' => $post->getId()])
            ->delete($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeNotInDatabase(Post::class, ['id' => $post->getId()]);
    }

    /** @test */
    public function it_batch_deletes_posts()
    {
        $this->factory->times(5)->create(Post::class);

        $url = $this->generateUrl('fludio.api_admin.batch_delete.post');

        $this
            ->delete($url, ['id' => [1, 2, 3]])
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeNotInDatabase(Post::class, ['id' => 1])
            ->seeNotInDatabase(Post::class, ['id' => 2])
            ->seeNotInDatabase(Post::class, ['id' => 3])
            ->seeInDatabase(Post::class, ['id' => 4])
            ->seeInDatabase(Post::class, ['id' => 5]);
    }
}
