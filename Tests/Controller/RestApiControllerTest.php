<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RestApiControllerTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiController', true);
        $kernel->setConfigFile('config_simple.yml');
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
    public function it_returns_multiple_posts()
    {
        $this->factory->times(2)->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index');

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'My Post', 'content' => 'bla']);
    }

    /** @test */
    public function it_returns_a_single_post()
    {
        $post = $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.show', ['id' => $post->getId()]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'My Post', 'content' => 'bla']);
    }

    /** @test */
    public function it_returns_404_if_entity_not_found()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.show', ['id' => 1]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(404);
    }

    /** @test */
    public function it_creates_a_new_post()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.create');

        $data = $this->factory->values(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $this
            ->post($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'My Post', 'content' => 'bla'])
            ->seeInDatabase(Post::class, ['title' => 'My Post', 'content' => 'bla']);
    }

    /** @test */
    public function it_batch_creates_new_entities()
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

    /** @test */
    public function it_handles_file_uploads()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.create');

        $root = vfsStream::setup();
        $root->addChild(vfsStream::newFile('photo.jpg'));
        $photoUrl = $root->getChild('photo.jpg')->url();

        $photo = new UploadedFile(
            $photoUrl,
            'photo.jpg',
            'image/jpeg',
            123
        );

        $this
            ->client->request(
                'POST',
                $url,
                ['title' => 'Photo post'],
                ['photo' => $photo],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['content' => 'Isn\'t that nice?'])
            );

        $this
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['photo' => $photoUrl]);
    }

    /** @test */
    public function it_updates_posts_with_put()
    {
        $post = $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.update', ['id' => $post->getId()]);

        $data = [
            'title' => $post->getTitle(),
            'content' => 'some_content',
        ];

        $this
            ->put($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => $post->getTitle()])
            ->seeInJson(['content' => 'some_content'])
            ->seeInDatabase(Post::class, $data);
    }

    /** @test */
    public function it_will_not_update_if_put_does_not_provide_all_data()
    {
        $post = $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.update', ['id' => $post->getId()]);

        $data = [
            'content' => 'some_content',
        ];

        $this
            ->put($url, $data)
            ->seeStatusCode(422)
            ->seeInDatabase(Post::class, ['title' => 'My Post', 'content' => 'bla']);
    }

    /** @test */
    public function it_updates_posts_with_patch()
    {
        $post = $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.update', ['id' => $post->getId()]);

        $data = [
            'content' => 'some_content',
        ];

        $this
            ->patch($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => $post->getTitle()])
            ->seeInJson(['content' => 'some_content'])
            ->seeInDatabase(Post::class, $data);
    }

    /** @test */
    public function it_batch_updates_posts()
    {
        $posts = $this->factory->times(5)->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.batch_update');

        $data = [
            [
                'id' => 1,
                'content' => 'some_content'
            ],
            [
                'id' => 2,
                'content' => 'some_content'
            ],
            [
                'id' => 3,
                'content' => 'some_content'
            ]
        ];

        $this
            ->patch($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInDatabase(Post::class, ['id' => 1, 'content' => 'some_content'])
            ->seeInDatabase(Post::class, ['id' => 2, 'content' => 'some_content'])
            ->seeInDatabase(Post::class, ['id' => 3, 'content' => 'some_content'])
            ->seeInDatabase(Post::class, ['id' => 4, 'content' => $posts[3]->getContent()])
            ->seeInDatabase(Post::class, ['id' => 5, 'content' => $posts[4]->getContent()])
            ->seeJsonHas('data', 3)
            ->seeInJson(['id' => 1, 'content' => 'some_content'])
            ->seeInJson(['id' => 2, 'content' => 'some_content'])
            ->seeInJson(['id' => 3, 'content' => 'some_content']);
    }

    /** @test */
    public function it_returns_validation_errors_for_batch_update()
    {
        $posts = $this->factory->times(5)->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.batch_update');

        $data = [
            [
                'id' => 1,
                'content' => ''
            ],
            [
                'id' => 2,
                'content' => 'Valid content'
            ],
            [
                'id' => 3,
                'content' => ''
            ]
        ];

        $this
            ->patch($url, $data)
            ->seeJsonResponse()
            ->seeStatusCode(422)
            ->seeNotInDatabase(Post::class, ['id' => 1, 'content' => 'some_content'])
            ->seeNotInDatabase(Post::class, ['id' => 2, 'content' => 'some_content'])
            ->seeNotInDatabase(Post::class, ['id' => 3, 'content' => 'some_content'])
            ->seeJsonHas('errors', 1)
            ->seeInJson(['entities' => [
                0 => ['content' => ['This value should not be blank.']],
                2 => ['content' => ['This value should not be blank.']],
            ]]);
    }

    /** @test */
    public function it_deletes_posts()
    {
        $post = $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.delete', ['id' => $post->getId()]);

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
        $this->factory->times(5)->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.batch_delete');

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

    /** @test */
    public function data_field_can_be_empty()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index');

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['data' => []]);
    }
}
