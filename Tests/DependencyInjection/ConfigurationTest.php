<?php

namespace BiteCodes\RestApiGeneratorBundle\Tests\DependencyInjection;

use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiManager;
use BiteCodes\RestApiGeneratorBundle\Api\Resource\ApiResource;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Index;
use BiteCodes\RestApiGeneratorBundle\Api\Actions\Show;
use BiteCodes\RestApiGeneratorBundle\DependencyInjection\Configuration;
use BiteCodes\RestApiGeneratorBundle\Form\DynamicFormType;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\app\AppKernel;
use BiteCodes\RestApiGeneratorBundle\Tests\Dummy\TestEntity\Post;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Symfony\Component\HttpKernel\Kernel;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    /** @test */
    public function assert_default_config()
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'entities' => [
                        Post::class => []
                    ]
                ]
            ],
            [
                'entities' => [
                    Post::class => [
                        'only' => ['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete'],
                        'except' => [],
                        'identifier' => 'id',
                        'filter' => null,
                        'form_type' => DynamicFormType::class,
                        'paginate' => false
                    ]
                ]
            ], 'entities');
    }

    /** @test */
    public function node_only_accepts_7_actions()
    {
        $this->assertConfigurationIsValid([
            [
                'entities' => [
                    Post::class => [
                        'only' => ['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete']
                    ]
                ]
            ]
        ], 'entities');
    }

    /** @test */
    public function node_only_raises_exception_for_other_values()
    {
        $this->assertConfigurationIsInvalid([
            [
                'entities' => [
                    Post::class => [
                        'only' => ['list']
                    ]
                ]
            ]
        ], 'entities');
    }

    /** @test */
    public function node_except_accepts_7_actions()
    {
        $this->assertConfigurationIsValid([
            [
                'entities' => [
                    Post::class => [
                        'except' => ['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete']
                    ]
                ]
            ]
        ], 'entities');
    }

    /** @test */
    public function node_except_raises_exception_for_other_values()
    {
        $this->assertConfigurationIsInvalid([
            [
                'entities' => [
                    Post::class => [
                        'except' => ['list']
                    ]
                ]
            ]
        ], 'entities');
    }

    /** @test */
    public function node_resource_name_can_not_be_empty()
    {
        $this->assertConfigurationIsInvalid([
            [
                'entities' => [
                    Post::class => [
                        'resource_name' => ''
                    ]
                ]
            ]
        ], 'entities');
    }

    /** @test */
    public function node_filter_can_not_be_empty()
    {
        $this->assertConfigurationIsInvalid([
            [
                'entities' => [
                    Post::class => [
                        'filter' => ''
                    ]
                ]
            ]
        ], 'entities');
    }

    /** @test */
    public function node_paginate_has_to_be_a_boolean_value()
    {
        $this->assertConfigurationIsInvalid([
            [
                'entities' => [
                    Post::class => [
                        'paginate' => 'yes'
                    ]
                ]
            ]
        ], 'entities');
    }
}
