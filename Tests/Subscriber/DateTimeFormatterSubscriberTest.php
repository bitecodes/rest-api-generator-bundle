<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Subscriber;

use Doctrine\ORM\Tools\SchemaTool;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Fludio\TestBundle\Test\TestCase;

class DateTimeFormatterSubscriberTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testDateTimeFormatterListener', false);
        $kernel->setConfigFile('config_listener.yml');
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
    public function it_returns_a_single_post()
    {
        $post = $this->factory->create(Post::class, [
            'title' => 'My Post',
            'content' => 'bla',
            'createdAt' => new \DateTime('2016-02-01 20:00:00')
        ]);

        $url = $this->generateUrl('fludio.rest_api_generator.show.posts', ['id' => $post->getId()]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['created_at' => '1454356800']);
    }
}