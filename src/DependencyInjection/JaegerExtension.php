<?php

declare(strict_types=1);

namespace Jaeger\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
    }
}
