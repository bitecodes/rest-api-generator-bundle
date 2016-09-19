<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Comment;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

class SerializationSubscriberTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testSerializationSubscriber', true);
        $kernel->setConfigFile('config_serialization.yml');
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
    public function it_handles_serialization_groups_exclude()
    {
        $this->factory->create(Post::class, [
            'title' => 'My Post 1',
            'content' => 'bla',
            'photo' => 'photo1.jpg',
            'comments' => $this->factory->create(Comment::class, [
                'body' => 'some comment'
            ]),
            'createdAt' => new \DateTime('2016-02-01 20:00:00')
        ]);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index');

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'My Post 1'])
            ->seeNotInJson(['body' => 'some comment']);
    }

    /** @test */
    public function it_handles_serialization_groups_expose()
    {
        $this->factory->create(Post::class, [
            'title' => 'My Post 1',
            'content' => 'bla',
            'photo' => 'photo1.jpg',
            'comments' => $this->factory->create(Comment::class, [
                'body' => 'some comment'
            ]),
            'createdAt' => new \DateTime('2016-02-01 20:00:00')
        ]);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.show', ['id' => 1]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'My Post 1'])
            ->seeInJson(['body' => 'some comment']);
    }

    /** @test */
    public function it_handles_custom_controller_with_api_serialization_interface()
    {
        $url = $this->generateUrl('test');

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['some' => true]);
    }

    /** @test */
    public function the_fields_to_serialize_can_be_provided()
    {
        $this->factory->create(Post::class, [
            'title' => 'My Post 1',
            'content' => 'bla',
            'photo' => 'photo1.jpg',
            'category' => $this->factory->create(Category::class, [
                'name' => 'some category'
            ]),
            'comments' => $this->factory->create(Comment::class, [
                'body' => 'some comment'
            ]),
            'createdAt' => new \DateTime('2016-02-01 20:00:00')
        ]);

        $fields = [
            'title',
            'content',
            'category' => ['name'],
            'comments' => ['id']
        ];

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.show', ['id' => 1, 'fields' => $fields]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'My Post 1'])
            ->seeInJson(['content' => 'bla'])
            ->seeNotInJson(['photo' => 'photo1.jpg'])
            ->seeInJson(['name' => 'some category'])
            ->seeInJson(['comments' => [['id' => 1]]]);
    }
}
