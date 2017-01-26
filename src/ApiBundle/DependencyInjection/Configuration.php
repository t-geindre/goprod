<?php
namespace ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('api_bundle');

        $rootNode
            ->children()
                ->arrayNode('github')
                    ->children()
                        ->arrayNode('urls')
                            ->children()
                                ->scalarNode('site')->end()
                                ->scalarNode('api')->end()
                            ->end()
                        ->end()
                        ->scalarNode('client_id')->end()
                        ->scalarNode('client_secret')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
