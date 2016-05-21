<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Annotation;

use BiteCodes\RestApiGeneratorBundle\Annotation\GenerateApiDoc;
use BiteCodes\RestApiGeneratorBundle\Annotation\GenerateApiDocHandler;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Create;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Index;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\Filter\PostFilter;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

class GenerateApiDocHandlerTest extends TestCase
{
    /**
     * @var \BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager
     */
    protected $manager;
    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testGenerateApiDocHandler', true);
        $kernel->setConfigFile('config_simple.yml');
        return $kernel;
    }

    public function setUp()
    {
        parent::setUp();

        $resource = new ApiResource('posts', [
            'entity' => Post::class,
            'filter' => PostFilter::class,
            'paginate' => true,
        ]);

        $resource->setConfigName('posts');

        $this->router = $this->client->getContainer()->get('router');

        $resource->addAction(new Index($this->router));
        $resource->addAction(new Create($this->router));

        $this->manager = new ApiManager();
        $this->manager->addResource($resource);
    }

    /** @test */
    public function it_adds_data_to_documentation()
    {
        $apiDoc = new ApiDoc([]);
        $generateApiDocAnnotation = new GenerateApiDoc([]);

        $route = new Route('/posts', ['_entity' => Post::class, '_roles' => ['ROLE_ADMIN']]);
        $route->setMethods(['GET']);

        $method = $this->getMockBuilder(\ReflectionMethod::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new GenerateApiDocHandler($this->manager, $this->em, $this->router, $this->client->getContainer());
        $handler->handle($apiDoc, [$generateApiDocAnnotation], $route, $method);

        $this->assertNull($apiDoc->getInput());
        $this->assertEquals(Post::class, $apiDoc->getOutput());
        $this->assertTrue($apiDoc->getAuthentication());
        $this->assertEquals(['ROLE_ADMIN'], $apiDoc->getAuthenticationRoles());
        $this->assertCount(4, $apiDoc->getFilters());
        $this->assertEquals('Posts', $apiDoc->getSection());
    }

    /** @test */
    public function it_adds_input_for_dynamic_form_type()
    {
        $apiDoc = new ApiDoc([]);
        $generateApiDocAnnotation = new GenerateApiDoc([]);

        $route = new Route('/posts', ['_entity' => Post::class, '_roles' => ['ROLE_ADMIN']]);
        $route->setMethods(['POST']);

        $method = $this->getMockBuilder(\ReflectionMethod::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new GenerateApiDocHandler($this->manager, $this->em, $this->router, $this->client->getContainer());
        $handler->handle($apiDoc, [$generateApiDocAnnotation], $route, $method);

        $this->assertCount(4, $apiDoc->getParameters());
        $this->assertEquals(Post::class, $apiDoc->getOutput());
    }
}
