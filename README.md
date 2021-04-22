# Symfony Bridge for Jaeger library

## Getting started

Register JaegerBundle like any other bundle for Symfony 4+

```php
// bundles.php
return [
    // ...
    \Jaeger\Symfony\JaegerBundle::class => ['all' => true],
    // ...
];
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


## Name generation options

You can specify just the suffix, if you name generator service is named as `jaeger.name.generator.*` or if you have any other naming scheme you can put the whole name into the configuration.
Keys are considered regular expressions that `route` of the request or `name` of the command should match to use alternative generator.
Expressions are checked top to bottom, if no match is found, default generator will be used

Example bundle config with name generation feature:

```yaml
# config/jaeger.yaml
jaeger:
  name_generator:
    max_length: 32
    command:  
      '.*': 'controller'
    request:
      'brand_routes_\d+': 'my_service_generator_alias'
```
