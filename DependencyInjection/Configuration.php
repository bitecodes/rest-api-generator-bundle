<?php

namespace Fludio\ApiAdminBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('fludio_api_admin');

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
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getOnlyNode()
    {
        $node = new ArrayNodeDefinition('only');

        $node->prototype('scalar')->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getExceptNode()
    {
        $node = new ArrayNodeDefinition('except');

        $node->prototype('scalar')->end();

        return $node;
    }

    private function getResourceNameNode()
    {
        return new ScalarNodeDefinition('resource_name');
    }
}
