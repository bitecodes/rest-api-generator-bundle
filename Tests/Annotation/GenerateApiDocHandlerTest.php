<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Annotation;

use BiteCodes\RestApiGeneratorBundle\Annotation\GenerateApiDoc;
use BiteCodes\RestApiGeneratorBundle\Annotation\GenerateApiDocHandler;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Index;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\Filter\PostFilter;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestCase;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

class GenerateApiDocHandlerTest extends TestCase
{
    /**
     * @var \BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $resource = new ApiResource('posts', [
            'entity' => Post::class,
            'filter' => PostFilter::class,
            'paginate' => true
        ]);

        $router = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource->addAction(new Index($router));

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

        $handler = new GenerateApiDocHandler($this->manager, $this->em);
        $handler->handle($apiDoc, [$generateApiDocAnnotation], $route, $method);

        $this->assertNull($apiDoc->getInput());
        $this->assertEquals(Post::class, $apiDoc->getOutput());
        $this->assertTrue($apiDoc->getAuthentication());
        $this->assertEquals(['ROLE_ADMIN'], $apiDoc->getAuthenticationRoles());
        $this->assertCount(5, $apiDoc->getFilters());
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

        $handler = new GenerateApiDocHandler($this->manager, $this->em);
        $handler->handle($apiDoc, [$generateApiDocAnnotation], $route, $method);

        $this->assertCount(3, $apiDoc->getParameters());
        $this->assertEquals(Post::class, $apiDoc->getOutput());
    }
}
