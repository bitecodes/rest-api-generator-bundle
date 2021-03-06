<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\Configuration;

use Doctrine\ORM\Tools\SchemaTool;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use BiteCodes\TestBundle\Test\TestCase;

class SecurityConfigTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testSecurityConfig', true);
        $kernel->setConfigFile('config_security.yml');
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
    public function it_denies_access_to_secured_routes_if_not_logged_in()
    {
        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('bite_codes.rest_api_generator.posts.show', ['id' => 1]);

        $this
            ->get($url)
            ->seeStatusCode(401);
    }

    /** @test */
    public function it_allows_access_if_user_has_role()
    {
        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('bite_codes.rest_api_generator.posts.show', ['id' => 1]);

        $this
            ->get($url, [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ])
            ->seeStatusCode(200);
    }

    /**
     * @test
     */
    public function it_denies_access_when_user_does_not_have_necessary_role()
    {
        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('bite_codes.rest_api_generator.posts.delete', ['id' => 1]);

        $this
            ->delete($url, [], [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ])
            ->seeStatusCode(403);
    }

    /** @test */
    public function it_allows_access_when_user_has_necessary_role()
    {
        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('bite_codes.rest_api_generator.posts.delete', ['id' => 1]);

        $this
            ->delete($url, [], [
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'admin',
            ])
            ->seeStatusCode(200);
    }

    /** @test */
    public function it_does_not_require_any_roles_if_route_is_not_secured()
    {
        $url = $this->getUrl('bite_codes.rest_api_generator.posts.index');

        $this
            ->get($url, [
                'PHP_AUTH_USER' => 'anon',
                'PHP_AUTH_PW' => 'anon',
            ])
            ->seeStatusCode(200);
    }
}
