<?php

namespace Fludio\ApiAdminBundle\Tests\DependencyInjection;

use Fludio\ApiAdminBundle\Resource\ResourceManager;
use Fludio\ApiAdminBundle\Resource\Resource;
use Fludio\ApiAdminBundle\DependencyInjection\FludioApiAdminExtension;
use Fludio\ApiAdminBundle\Tests\Dummy\TestEntity\Post;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class FludioApiAdminExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new FludioApiAdminExtension()
        ];
    }

    /** @test */
    public function container_builder_has_endpoint_manager()
    {
        $this->load($this->getConfig());

        $this->assertContainerBuilderHasService('fludio.api_admin.endpoint_manager', ResourceManager::class);
    }

    /** @test */
    public function container_builder_has_entity_endpoint_configurations()
    {
        $this->load($this->getConfig());

        $this->assertContainerBuilderHasService('fludio.api_admin.post', Resource::class);
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