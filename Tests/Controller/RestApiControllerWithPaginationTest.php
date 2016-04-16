<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Fludio\TestBundle\Test\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RestApiControllerWithPaginationTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testRestApiControllerWithPagination', false);
        $kernel->setConfigFile('config_pagination.yml');
        return $kernel;
    }

    public function setUp()
    {
        parent::setUp();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        $this->factory->create(Post::class, ['title' => 'Post 1', 'content' => 'My post content']);
        $this->factory->create(Post::class, ['title' => 'Post 2', 'content' => 'Something else']);
        $this->factory->create(Post::class, ['title' => 'Post 3', 'content' => 'Even more']);
        $this->factory->create(Post::class, ['title' => 'Post 4', 'content' => 'Last one']);
        $this->factory->create(Post::class, ['title' => 'Post 5', 'content' => 'Last one']);
    }

    /** @test */
    public function it_paginates_posts()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index', ['page' => 2, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL);
        $first = $prev = $this->generateUrl('bite_codes.rest_api_generator.posts.index', ['page' => 1, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL);
        $last = $next = $this->generateUrl('bite_codes.rest_api_generator.posts.index', ['page' => 3, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeNotInJson(['title' => 'Post 1'])
            ->seeNotInJson(['title' => 'Post 2'])
            ->seeInJson(['title' => 'Post 3'])
            ->seeInJson(['title' => 'Post 4'])
            ->seeNotInJson(['title' => 'Post 5'])
            ->seeInJson(['meta' => ['total' => 5]])
            ->seeJsonHas('links', 5)
            ->seeInJson(['first' => $first])
            ->seeInJson(['prev' => $prev])
            ->seeInJson(['current' => $url])
            ->seeInJson(['next' => $next])
            ->seeInJson(['last' => $last]);
    }

    /** @test */
    public function it_returns_null_for_previous_if_there_is_no_previous_page()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index', ['page' => 1, 'limit' => 2]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Post 1'])
            ->seeInJson(['title' => 'Post 2'])
            ->seeNotInJson(['title' => 'Post 3'])
            ->seeNotInJson(['title' => 'Post 4'])
            ->seeNotInJson(['title' => 'Post 5'])
            ->seeInJson(['prev' => null]);
    }

    /** @test */
    public function it_returns_null_for_next_if_there_is_no_next_page()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index', ['page' => 3, 'limit' => 2]);

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeNotInJson(['title' => 'Post 1'])
            ->seeNotInJson(['title' => 'Post 2'])
            ->seeNotInJson(['title' => 'Post 3'])
            ->seeNotInJson(['title' => 'Post 4'])
            ->seeInJson(['title' => 'Post 5'])
            ->seeInJson(['next' => null]);
    }

    /** @test */
    public function it_uses_default_values_if_not_given_in_query()
    {
        $url = $this->generateUrl('bite_codes.rest_api_generator.posts.index');

        $this
            ->get($url)
            ->seeJsonResponse()
            ->seeStatusCode(200)
            ->seeInJson(['title' => 'Post 1'])
            ->seeInJson(['title' => 'Post 2'])
            ->seeInJson(['title' => 'Post 3'])
            ->seeInJson(['title' => 'Post 4'])
            ->seeInJson(['title' => 'Post 5']);
    }
}
