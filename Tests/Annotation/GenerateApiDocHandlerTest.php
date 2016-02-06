<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Annotation;

use Fludio\RestApiGeneratorBundle\Annotation\GenerateApiDoc;
use Fludio\RestApiGeneratorBundle\Annotation\GenerateApiDocHandler;
use Fludio\RestApiGeneratorBundle\Form\DynamicFormType;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\Filter\PostFilter;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

class GenerateApiDocHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceManager
     */
    protected $manager;

    public function setUp()
    {
        $resource = new Resource(Post::class, [
            'filter' => PostFilter::class,
            'paginate' => true
        ]);

        $this->manager = new ResourceManager();
        $this->manager->addConfiguration($resource);
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

        $handler = new GenerateApiDocHandler($this->manager);
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

        $handler = new GenerateApiDocHandler($this->manager);
        $handler->handle($apiDoc, [$generateApiDocAnnotation], $route, $method);

        $this->assertEquals(DynamicFormType::class, $apiDoc->getInput());
//        $this->assertCount(3, $apiDoc->getParameters());
        $this->assertEquals(Post::class, $apiDoc->getOutput());
    }
}
