<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Subscriber;

use Doctrine\ORM\Tools\SchemaTool;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Fludio\TestBundle\Test\TestCase;

class ApiExceptionSubscriberTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testApiExceptionSubscriber', false);
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
    public function it_catches_404_errors()
    {
        $this
            ->get('/api/some/fake')
            ->seeStatusCode(404)
            ->seeJsonResponse()
            ->seeInJson(['title' => 'Not Found'])
            ->seeJsonHas('detail');
    }

    /** @test */
    public function it_catches_entity_not_found_errors()
    {
        $url = $this->generateUrl('fludio.rest_api_generator.show.posts', ['id' => 1]);

        $this
            ->get($url)
            ->seeStatusCode(404)
            ->seeJsonResponse()
            ->seeInJson(['type' => 'entity_not_found'])
            ->seeJsonHas('title');
    }

    /** @test */
    public function it_catches_invalid_json()
    {
        $url = $this->generateUrl('fludio.rest_api_generator.create.posts');

        $invalidBody = <<<EOF
{
    "title" : "My title"
    "content": "I'm from a test!"
}
EOF;

        $this
            ->json('POST', $url, $invalidBody)
            ->seeStatusCode(400)
            ->seeJsonResponse()
            ->seeInJson(['title' => 'Invalid JSON format sent']);
    }

    /** @test */
    public function it_catches_validation_errors()
    {
        $url = $this->generateUrl('fludio.rest_api_generator.create.posts');

        $data = ['content' => 'My Post Content'];

        $this
            ->post($url, $data)
            ->seeStatusCode(422)
            ->seeJsonResponse()
            ->seeJsonHas('errors.title')
            ->seeInJson(['title' => 'There was a validation error']);
    }
}
