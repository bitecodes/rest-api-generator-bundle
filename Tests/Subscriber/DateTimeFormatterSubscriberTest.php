<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Subscriber;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;

class DateTimeFormatterSubscriberTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testDateTimeFormatterSubscriber', true);
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
    public function it_serializes_date_times()
    {
        $this->factory->create(Post::class, [
            'title' => 'My Post 1',
            'content' => 'bla',
            'createdAt' => new \DateTime('2016-02-01 20:00:00')
        ]);

        $this->factory->create(Post::class, [
            'title' => 'My Post 2',
            'content' => 'bla',
            'createdAt' => new \DateTime('2016-02-02 20:00:00')
        ]);

        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index');

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['created_at' => '1454356800'])
            ->seeInJson(['created_at' => '1454443200']);

    }
}