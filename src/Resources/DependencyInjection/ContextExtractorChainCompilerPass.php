<?php
namespace Jaeger\Symfony\Resources\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContextExtractorChainCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('jaeger.context.extractor.chain')) {
            throw new \RuntimeException(
                sprintf('Required service %s is missing from container', 'jaeger.context.extractor.chain')
            );
        }

        $definition = $container->getDefinition('jaeger.context.extractor.chain');
        foreach ($container->findTaggedServiceIds('jaeger.context.extractor') as $id => $tags) {
            foreach ($tags as $tag) {
                $priority = array_key_exists('priority', $tag) ? $tag['priority'] : 0;
                $definition->addMethodCall('add', [new Reference($id), $priority]);
            }
        }
    }
}
