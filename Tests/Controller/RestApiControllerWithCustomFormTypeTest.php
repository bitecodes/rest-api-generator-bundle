<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\TestBundle\Test\DatabaseReset;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

class RestApiControllerWithCustomFormTypeTest extends TestCase
{
    use DatabaseReset;

    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithCustomFormType', true);
        $kernel->setConfigFile('config_custom_form_type.yml');
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
    public function it_uses_a_custom_form_type_to_create_a_resource()
    {
        $params = [
            'title' => 'My post',
            'content' => 'some content'
        ];

        $this
            ->post('/posts', $params)
            ->seeJsonResponse()
            ->seeStatusCode(200);
    }
}