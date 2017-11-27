# Symfony Bridge for Jaeger library

## Getting started
Add this line to your services.yml file
```
imports:
- { resource: '../../vendor/code-tool/jaeger-client-symfony-bridge/src/Resources/config/services.yml' }
```
and register this compiler pass in your Kernel
```$xslt
Kernel::build
/**
     * @inheritDoc
     */
    protected function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        parent::build($container);
        $container
            ->addCompilerPass(
                new \Jaeger\Symfony\Resources\DependencyInjection\CodecRegistryCompilerPass()
            );
    }
```
## Proper bundle/plugin files are coming soon
