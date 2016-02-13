<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Annotation;

use Fludio\RestApiGeneratorBundle\Annotation\GenerateApiDoc;
use Fludio\RestApiGeneratorBundle\Annotation\GenerateApiDocHandler;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiResource;
use Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager;
use Fludio\RestApiGeneratorBundle\Api\Routing\Action\Index;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\Filter\PostFilter;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestCase;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

class GenerateApiDocHandlerTest extends TestCase
{
    /**
     * @var \Fludio\RestApiGeneratorBundle\Api\Resource\ApiManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $resource = new ApiResource(Post::class, [
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
