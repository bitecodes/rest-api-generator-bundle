<?php

namespace Fludio\RestApiGeneratorBundle\DependencyInjection;

use Fludio\RestApiGeneratorBundle\Resource\ResourceOptions;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fludio_rest_api_generator');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->prototype('array')
                        ->children()
                            ->append($this->getOnlyNode())
                            ->append($this->getExceptNode())
                            ->append($this->getResourceNameNode())
                            ->append($this->getSecureNode())
                            ->append($this->getListenerNode())
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * Node for routing - only
     *
     * @return ArrayNodeDefinition
     */
    private function getOnlyNode()
    {
        $node = new ArrayNodeDefinition('only');

        $node->prototype('scalar')->end();

        return $node;
    }

    /**
     * Node for routing - except
     *
     * @return ArrayNodeDefinition
     */
    private function getExceptNode()
    {
        $node = new ArrayNodeDefinition('except');

        $node->prototype('scalar')->end();

        return $node;
    }

    /**
     * Node for resource name
     *
     * @return ScalarNodeDefinition
     */
    private function getResourceNameNode()
    {
        return new ScalarNodeDefinition('resource_name');
    }

    /**
     * Node for security settings
     *
     * @return ArrayNodeDefinition
     */
    private function getSecureNode()
    {
        $node = new ArrayNodeDefinition('secure');

        $routesNode = new ArrayNodeDefinition('routes');

        $routesNode
            ->children()
                ->arrayNode('index')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('show')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('create')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('update')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('batch_update')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('delete')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('batch_delete')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        $node
            ->children()
                ->arrayNode('default')
                    ->prototype('scalar')->end()
                ->end()
                ->append($routesNode)
            ->end();

        $routesNode
            ->beforeNormalization()
                ->always(function($val) {
                    foreach(ResourceOptions::$allActions as $action) {
                        if(!isset($val[$action])) {
                            $val[$action] = ['MY', 'EMPTY', 'ARRAY'];
                        }
                    }

                    return $val;
                })
            ->end()
            ->validate()
                ->always(function($val) {
                    foreach(ResourceOptions::$allActions as $action) {
                        if (!empty($val[$action]) && $val[$action] == ['MY', 'EMPTY', 'ARRAY']) {
                            unset($val[$action]);
                        }
                    }

                    return $val;
                })
            ->end();

        return $node;
    }

    /**
     * Node for listener settings
     *
     * @return ArrayNodeDefinition
     */
    private function getListenerNode()
    {
        $node = new ArrayNodeDefinition('listener');

        $node
            ->children()
                ->scalarNode('datetime')->end()
            ->end();

        return $node;
    }
}
