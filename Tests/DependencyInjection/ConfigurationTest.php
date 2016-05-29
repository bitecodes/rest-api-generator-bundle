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
                    'resources' => [
                        'posts' => [
                            'entity' => Post::class,
                        ]
                    ]
                ]
            ],
            [
                'resources' => [
                    'posts' => [
                        'entity' => Post::class,
                        'routes' => [
                            'index' => [
                                'security' => null
                            ],
                            'show' => [
                                'security' => null
                            ],
                            'create' => [
                                'security' => null
                            ],
                            'update' => [
                                'security' => null
                            ],
                            'batch_update' => [
                                'security' => null
                            ],
                            'delete' => [
                                'security' => null
                            ],
                            'batch_delete' => [
                                'security' => null
                            ],
                        ],
                        'identifier' => 'id',
                        'filter' => null,
                        'form_type' => DynamicFormType::class,
                        'pagination' => [
                            'enabled' => false,
                            'limit' => 10
                        ],
                        'is_main_resource' => true,
                        'sub_resources' => []
                    ]
                ]
            ], 'resources');
    }

    /** @test */
    public function node_only_accepts_7_actions()
    {
        $this->assertConfigurationIsValid([
            [
                'resources' => [
                    'posts' => [
                        'entity' => Post::class,
                        'routes' => [
                            'index' => [],
                            'show' => [],
                            'create' => [],
                            'update' => [],
                            'batch_update' => [],
                            'delete' => [],
                            'batch_delete' => []
                        ]
                    ]
                ]
            ]
        ], 'resources');
    }

    /** @test */
    public function node_only_raises_exception_for_other_values()
    {
        $this->assertConfigurationIsInvalid([
            [
                'resources' => [
                    'posts' => [
                        'entity' => Post::class,
                        'routes' => [
                            'list' => [],
                        ]
                    ]
                ]
            ]
        ], 'resources');
    }

    /** @test */
    public function node_resource_name_can_not_be_empty()
    {
        $this->assertConfigurationIsInvalid([
            [
                'resources' => [
                    'posts' => [
                        'entity' => Post::class,
                        'resource_name' => ''
                    ]
                ]
            ]
        ], 'resources');
    }

    /** @test */
    public function node_filter_can_not_be_empty()
    {
        $this->assertConfigurationIsInvalid([
            [
                'resources' => [
                    'posts' => [
                        'entity' => Post::class,
                        'filter' => ''
                    ]
                ]
            ]
        ], 'resources');
    }

    /** @test */
    public function node_paginate_has_to_be_a_boolean_value()
    {
        $this->assertConfigurationIsInvalid([
            [
                'resources' => [
                    'posts' => [
                        'entity' => Post::class,
                        'paginate' => 'yes'
                    ]
                ]
            ]
        ], 'resources');
    }
}
