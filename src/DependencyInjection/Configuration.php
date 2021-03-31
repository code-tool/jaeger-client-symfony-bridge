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
        if (true === method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('jaeger', 'array');
        }

        // @formatter:off
        $rootNode
            ->children()
                ->arrayNode('denylist')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('operation_names')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
