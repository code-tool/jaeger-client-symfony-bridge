<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Resources\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DebugExtractorChainCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('jaeger.debug.extractor.chain')) {
            throw new \RuntimeException(
                sprintf('Required service %s is missing from container', 'jaeger.debug.extractor.chain')
            );
        }

        $definition = $container->getDefinition('jaeger.debug.extractor.chain');
        foreach ($container->findTaggedServiceIds('jaeger.debug.extractor') as $id => $tags) {
            foreach ($tags as $tag) {
                $priority = $tag['priority'] ?? 0;
                $definition->addMethodCall('add', [new Reference($id), $priority]);
            }
        }
    }
}
