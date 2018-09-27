<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Subscriber;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;

class ApiExceptionSubscriberTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testApiExceptionSubscriber', true);
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
        $this->markTestSkipped();

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
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.show', ['id' => 1]);

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
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.create');

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
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.create');

        $data = ['content' => 'My Post Content'];

        $this
            ->post($url, $data)
            ->seeStatusCode(422)
            ->seeJsonResponse()
            ->seeJsonHas('errors.title')
            ->seeInJson(['title' => 'There was a validation error']);
    }
}
