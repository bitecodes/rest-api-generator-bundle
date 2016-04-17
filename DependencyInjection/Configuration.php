<?php

namespace BiteCodes\RestApiGeneratorBundle\DependencyInjection;

use BiteCodes\RestApiGeneratorBundle\Form\DynamicFormType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
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
        $rootNode = $treeBuilder->root('bite_codes_rest_api_generator');

        $rootNode
            ->children()
                ->append($this->getListenerNode())
                ->arrayNode('resources')
                    ->prototype('array')
                        ->children()
                            ->append($this->getEntityNode())
                            ->append($this->getOnlyNode())
                            ->append($this->getExceptNode())
                            ->append($this->getIdentifierNode())
                            ->append($this->getSecureNode())
                            ->append($this->getFilterNode())
                            ->append($this->getFormTypeNode())
                            ->append($this->getPaginationNode())
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function getEntityNode()
    {
        $node = new ScalarNodeDefinition('entity');

        $node
            ->isRequired()
            ->cannotBeEmpty()
            ->end();

        return $node;
    }

    /**
     * Node for routing - only
     *
     * @return ArrayNodeDefinition
     */
    private function getOnlyNode()
    {
        $node = new ArrayNodeDefinition('only');

        $node
            ->defaultValue(['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete'])
            ->prototype('scalar')
            ->validate()
            ->ifNotInArray(['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete'])
                ->thenInvalid('Invalid action for only: "%s"')
            ->end();

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

        $node
            ->defaultValue([])
            ->prototype('scalar')
            ->validate()
            ->ifNotInArray(['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete'])
                ->thenInvalid('Invalid action for except: "%s"')
            ->end();

        return $node;
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
                    foreach(ConfigurationProcessor::$allActions as $action) {
                        if(!isset($val[$action])) {
                            $val[$action] = ['MY', 'EMPTY', 'ARRAY'];
                        }
                    }

                    return $val;
                })
            ->end()
            ->validate()
                ->always(function($val) {
                    foreach(ConfigurationProcessor::$allActions as $action) {
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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('datetime')->defaultFalse()->end()
            ->end();

        return $node;
    }

    /**
     * @return ScalarNodeDefinition
     */
    private function getFilterNode()
    {
        $node = new ScalarNodeDefinition('filter');

        $node
            ->defaultNull()
            ->cannotBeEmpty()
            ->end();

        return $node;
    }

    /**
     * @return BooleanNodeDefinition
     */
    private function getPaginationNode()
    {
        $node = new BooleanNodeDefinition('paginate');

        $node
            ->defaultValue(false)
            ->treatNullLike(false)
            ->defaultFalse();

        return $node;
    }

    private function getFormTypeNode()
    {
        $node = new ScalarNodeDefinition('form_type');

        $node
            ->defaultValue(DynamicFormType::class);

        return $node;
    }

    private function getIdentifierNode()
    {
        $node = new ScalarNodeDefinition('identifier');

        $node
            ->defaultValue('id');

        return $node;
    }
}
