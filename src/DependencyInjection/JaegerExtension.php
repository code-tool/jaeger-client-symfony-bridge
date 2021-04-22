<?php
declare(strict_types=1);

namespace Jaeger\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class JaegerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources')
        );
        $loader->load('services.yml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        if ($this->isConfigEnabled($container, $config['denylist'])) {
            $container->setParameter('jaeger.sampler.operation_denylist', $config['denylist']['operation_names']);
            $loader->load('denylist.yml');
        }
        if ($this->isConfigEnabled($container, $config['name_generator'])) {
            $container->setParameter('jaeger.name.max_length', (int)$config['name_generator']['max_length']);
            foreach ($config['name_generator']['request'] as $item => $alias) {
                $container->getDefinition('jaeger.name.generator.request')->addMethodCall(
                    'add',
                    [$item, new Reference($alias)]
                );
            }
            foreach ($config['name_generator']['command'] as $item => $alias) {
                $container->getDefinition('jaeger.name.generator.command')->addMethodCall(
                    'add',
                    [$item, new Reference($alias)]
                );
            }
        }
    }
}
