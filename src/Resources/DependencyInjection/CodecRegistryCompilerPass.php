<?php
declare(strict_types=1);

namespace CodeTool\Opentracing\Symfony\Resources\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CodecRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('jaeger.codec.registry')) {
            throw new \RuntimeException(
                sprintf('Required service %s is missing from container', 'codec.registry')
            );
        }

        $definition = $container->getDefinition('jaeger.codec.registry');
        foreach ($container->findTaggedServiceIds('jaeger.codec') as $id => $tags) {
            foreach ($tags as $tag) {
                if (false === array_key_exists('alias', $tag)) {
                    throw new \RuntimeException(
                        sprintf('Required tag field %s is missing from definition', 'alias')
                    );
                }
                $definition->addMethodCall('offsetSet', [$tag['alias'], new Reference($id)]);
            }
        }
    }
}
