<?php

namespace Fludio\RestApiGeneratorBundle\Tests\Configuration;

use Doctrine\ORM\Tools\SchemaTool;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Fludio\TestBundle\Test\TestCase;

class SecurityConfigTest extends TestCase
{
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel('testSecurityConfigTest', true);
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

        $url = $this->getUrl('fludio.rest_api_generator.show.posts', ['id' => 1]);

        $this
            ->get($url)
            ->seeStatusCode(401);
    }

    /** @test */
    public function it_allows_access_if_user_has_role()
    {
        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('fludio.rest_api_generator.show.posts', ['id' => 1]);

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
        $this->markTestSkipped('Implement error messages');

        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('fludio.rest_api_generator.delete.posts', ['id' => 1]);

        $this
            ->delete($url, [], [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'user',
            ])
            ->seeStatusCode(401);
    }

    /** @test */
    public function it_allows_access_when_user_has_necessary_role()
    {
        $this->factory->create(Post::class, ['title' => 'My Post', 'content' => 'bla']);

        $url = $this->getUrl('fludio.rest_api_generator.delete.posts', ['id' => 1]);

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
        $url = $this->getUrl('fludio.rest_api_generator.index.posts');

        $this
            ->get($url, [
                'PHP_AUTH_USER' => 'anon',
                'PHP_AUTH_PW' => 'anon',
            ])
            ->seeStatusCode(200);
    }
}
