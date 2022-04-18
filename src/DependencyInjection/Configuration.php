<?php
declare(strict_types=1);

namespace Jaeger\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('jaeger');

        // @formatter:off
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('denylist')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('operation_names')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('name_generator')
                    ->canBeEnabled()
                    ->children()
                        ->integerNode('max_length')->defaultValue(64)->end()
                        ->arrayNode('command')
                            ->useAttributeAsKey('pattern')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('request')
                            ->useAttributeAsKey('pattern')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
