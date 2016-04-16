<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\DependencyInjection;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\BiteCodesRestApiGeneratorExtension;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\FludioRestApiGeneratorExtension;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class FludioApiAdminExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new BiteCodesRestApiGeneratorExtension()
        ];
    }

    /** @test */
    public function container_builder_has_endpoint_manager()
    {
        $this->load($this->getConfig());

        $this->assertContainerBuilderHasService('bite_codes.rest_api_generator.endpoint_manager', ApiManager::class);
    }

    /** @test */
    public function container_builder_has_entity_endpoint_configurations()
    {
        $this->load($this->getConfig());

        $this->assertContainerBuilderHasService('bite_codes.rest_api_generator.posts', ApiResource::class);
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