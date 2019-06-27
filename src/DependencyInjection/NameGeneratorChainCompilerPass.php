<?php
declare(strict_types=1);

namespace Jaeger\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NameGeneratorChainCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('jaeger.name.generator.chain')) {
            throw new \RuntimeException(
                sprintf('Required service %s is missing from container', 'jaeger.name.generator.chain')
            );
        }

        $definition = $container->getDefinition('jaeger.name.generator.chain');
        foreach ($container->findTaggedServiceIds('jaeger.name.generator') as $id => $tags) {
            foreach ($tags as $tag) {
                $priority = $tag['priority'] ?? 0;
                $definition->addMethodCall('add', [new Reference($id), $priority]);
            }
        }
    }
}
