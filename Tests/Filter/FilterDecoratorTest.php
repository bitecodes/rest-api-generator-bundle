<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Filter;

use BiteCodes\DoctrineFilter\FilterBuilder;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Filter\FilterDecorator;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Category;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;
use Doctrine\ORM\Tools\SchemaTool;

class FilterDecoratorTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testFilterDecorator', true);
        $kernel->setConfigFile('config_sub_resources.yml');
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
    public function it_adds_filters_to_query_on_the_parent_resources()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('x')
            ->from(Post::class, 'x');

        $manager = $this->getMockBuilder(ApiManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBundlePrefix'])
            ->getMock();

        $manager
            ->method('getBundlePrefix')
            ->willReturn('some.prefix');

        $categoryResource = new ApiResource('categories', ['entity' => Category::class]);
        $categoryResource->setManager($manager);

        $parentResources = [
            1 => $categoryResource
        ];

        $filter = new FilterDecorator($parentResources);
        $builder = new FilterBuilder();
        $builder->setQueryBuilder($qb);

        $filter->buildFilter($builder);

        $filters = $builder->getFilters();

        $this->assertCount(1, $filters);
        $this->assertTrue(isset($filters[FilterDecorator::getFilterName($categoryResource)]));
        $filter = $filters[FilterDecorator::getFilterName($categoryResource)];
        $this->assertEquals(['category.id'], $filter->getFields());
    }
}
