<?php

namespace BiteCodes\RestApiGeneratorBundle\DependencyInjection;

use BiteCodes\RestApiGeneratorBundle\Api\Actions\Actions;
use BiteCodes\RestApiGeneratorBundle\Form\DynamicFormType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    protected $allRoutes = ['index', 'show', 'create', 'update', 'batch_update', 'delete', 'batch_delete'];

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
                            ->append($this->getRoutesNode())
                            ->append($this->getPrefixNode())
                            ->append($this->getIdentifierNode())
                            ->append($this->getSecureNode())
                            ->append($this->getFilterNode())
                            ->append($this->getFormTypeNode())
                            ->append($this->getPaginationNode())
                            ->append($this->getIsMainResourceNode())
                            ->append($this->getSubResourcesNode())
                        ->end()
                        ->validate()
                            ->ifTrue(function($v) {
                                if(!isset($v['routes'])) {
                                    return false;
                                }

                                $routes = array_keys($v['routes']);

                                return count(array_diff($routes, $this->allRoutes)) > 0;
                            })
                            ->thenInvalid(sprintf('Invalid route name. Only %s allowed', join(', ', array_map(function($r) {
                                return "'$r'";
                            }, $this->allRoutes))))
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(function($v) {
                                return !isset($v['routes']);
                            })
                            ->then(function($v) {
                                $v['routes'] = [
                                    'index' => [],
                                    'show' => [],
                                    'create' => [],
                                    'update' => [],
                                    'batch_update' => [],
                                    'delete' => [],
                                    'batch_delete' => [],
                                ];
                                return $v;
                            })
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
    private function getRoutesNode()
    {
        $node = new ArrayNodeDefinition('routes');

        $node
            ->prototype('array')
                ->children()
                    ->scalarNode('security')
                        ->defaultNull()
                    ->end()
                    ->arrayNode('serialization_groups')
                        ->prototype('scalar')->end()
                        ->defaultValue(['Default'])
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Node for routing - only
     *
     * @return ArrayNodeDefinition
     */
    private function getPrefixNode()
    {
        $node = new ScalarNodeDefinition('prefix');

        $node
            ->defaultValue('')
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
                ->scalarNode('default')
                    ->defaultNull()
                ->end()
                ->end()
                ->append($routesNode)
            ->end();

        $routesNode
            ->beforeNormalization()
                ->always(function($val) {
                    foreach(Actions::$all as $action) {
                        if(!isset($val[$action])) {
                            $val[$action] = ['MY', 'EMPTY', 'ARRAY'];
                        }
                    }

                    return $val;
                })
            ->end()
            ->validate()
                ->always(function($val) {
                    foreach(Actions::$all as $action) {
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
        $node = new ArrayNodeDefinition('pagination');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')
                    ->defaultFalse()
                    ->treatNullLike(false)
                ->end()
                ->scalarNode('limit')
                    ->defaultValue(10)
                ->end()
            ->end();

        return $node;
    }

    /**
     * @return ScalarNodeDefinition
     */
    private function getFormTypeNode()
    {
        $node = new ScalarNodeDefinition('form_type');

        $node
            ->defaultValue(DynamicFormType::class);

        return $node;
    }

    /**
     * @return ScalarNodeDefinition
     */
    private function getIdentifierNode()
    {
        $node = new ScalarNodeDefinition('identifier');

        $node
            ->defaultValue('id');

        return $node;
    }

    /**
     * @return BooleanNodeDefinition
     */
    private function getIsMainResourceNode()
    {
        $node = new BooleanNodeDefinition('is_main_resource');

        $node
            ->defaultTrue();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getSubResourcesNode()
    {
        $node = new ArrayNodeDefinition('sub_resources');

        $node
            ->defaultValue([])
            ->prototype('array')
                ->children()
                    ->scalarNode('assoc_parent')->end()
                    ->scalarNode('assoc_sub')->end()
                ->end()
            ->end();

        return $node;
    }
}
