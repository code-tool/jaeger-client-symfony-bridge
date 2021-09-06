<?php
declare(strict_types=1);

namespace Jaeger\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\AliasDeprecatedPublicServicesPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeprecatedAliasesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->getAlias('jaeger.span.handler.gloabal')
            ->setDeprecated(
                ...
                $this->getDeprecationMsg(
                    'The "%alias_id%" service is deprecated. Use "jaeger.span.handler.global".',
                    '3.5.0'
                )
            );
    }

    /**
     * Returns the correct deprecation param's as an array for setDeprecated.
     *
     * symfony/dependency-injection v5.1 introduces a deprecation notice when calling
     * setDeprecation() with less than 3 args and the
     * `Symfony\Component\DependencyInjection\Compiler\AliasDeprecatedPublicServicesPass` class was
     * introduced at the same time. By checking if this class exists,
     * we can determine the correct param count to use when calling setDeprecated.
     *
     * @param string $message
     * @param string $version
     *
     * @return array
     */
    private function getDeprecationMsg(string $message, string $version): array
    {
        if (class_exists(AliasDeprecatedPublicServicesPass::class)) {
            return [
                'code-tool/jaeger-client-symfony-bridge',
                $version,
                $message,
            ];
        }

        return [true, $message];
    }
}
