# Symfony Bridge for Jaeger library

## Getting started
Register JaegerBundle like any other bundle for Symfony 4
```
bundles.php
[
...
\Jaeger\Symfony\JaegerBundle::class => ['all' => true],
...
]
```
OR for Symfony >=2
```
public function registerBundles()
    {
        $bundles = [
            ...
            new \Jaeger\Symfony\JaegerBundle(),
            ...
        ]
    }
```

## Denylist sampling operations 

This feature allows to disable sampling for deny-listed operations.

It will be useful when your infrastructure initiates some operations which you are
not insterested for tracking in Jaeger.

Operation names used in package configuration refers to first (parent) span operation name
(https://www.jaegertracing.io/docs/latest/architecture/#span).

> Hint: if you use default name generator (class `\Jaeger\Symfony\Name\Generator\DefaultNameGenerator`),
> your operation name for HTTP requests will be the same as matched symfony route name.

Example bundle config with denylist feature:

```yaml
# config/jaeger.yaml
jaeger:
  denylist:
    operation_names:
      - 'healthcheck'
      - 'metrics'
```
