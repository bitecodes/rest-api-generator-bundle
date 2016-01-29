<?php

namespace Fludio\RestApiGeneratorBundle\Tests\DependencyInjection;

use Fludio\RestApiGeneratorBundle\Resource\ResourceManager;
use Fludio\RestApiGeneratorBundle\Resource\Resource;
use Fludio\RestApiGeneratorBundle\DependencyInjection\FludioRestApiGeneratorExtension;
use Fludio\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class FludioApiAdminExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new FludioRestApiGeneratorExtension()
        ];
    }

    /** @test */
    public function container_builder_has_endpoint_manager()
    {
        $this->load($this->getConfig());

        $this->assertContainerBuilderHasService('fludio.rest_api_generator.endpoint_manager', ResourceManager::class);
    }

    /** @test */
    public function container_builder_has_entity_endpoint_configurations()
    {
        $this->load($this->getConfig());

        $this->assertContainerBuilderHasService('fludio.rest_api_generator.post', Resource::class);
    }

    protected function getConfig()
    {
        return [
            'entities' => [
                Post::class => []
            ]
        ];
    }
}