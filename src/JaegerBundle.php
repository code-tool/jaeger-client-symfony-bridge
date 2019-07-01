<?php

declare(strict_types=1);

namespace Jaeger\Symfony;

use Jaeger\Symfony\DependencyInjection\CodecRegistryCompilerPass;
use Jaeger\Symfony\DependencyInjection\ContextExtractorChainCompilerPass;
use Jaeger\Symfony\DependencyInjection\DebugExtractorChainCompilerPass;
use Jaeger\Symfony\DependencyInjection\NameGeneratorChainCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JaegerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new CodecRegistryCompilerPass())
            ->addCompilerPass(new ContextExtractorChainCompilerPass())
            ->addCompilerPass(new DebugExtractorChainCompilerPass())
            ->addCompilerPass(new NameGeneratorChainCompilerPass());
    }
}
