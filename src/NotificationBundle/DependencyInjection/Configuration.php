<?php
namespace NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('notification');

        $rootNode
            ->children()
                ->arrayNode('slack')
                    ->children()
                        ->scalarNode('icon')->end()
                        ->scalarNode('name')
                            ->defaultValue('Goprod')
                        ->end()
                        ->arrayNode('notifications')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('owner')->end()
                                    ->scalarNode('repository')->end()
                                    ->booleanNode('attach_pullrequest')
                                        ->defaultValue(false)
                                    ->end()
                                    ->scalarNode('channel')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                        ->validate()
                                        ->ifTrue(function ($value) {
                                            return !(substr($value, 0, 1) == '#');
                                        })
                                            ->thenInvalid('Channel name must start with a "#"')
                                        ->end()
                                    ->end()
                                    ->scalarNode('status')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                        ->validate()
                                        ->ifNotInArray(array('new', 'done', 'canceled'))
                                            ->thenInvalid('Invalid status %s')
                                        ->end()
                                    ->end()
                                    ->scalarNode('message')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
