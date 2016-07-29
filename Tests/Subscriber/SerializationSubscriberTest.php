<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Subscriber;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
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
}
