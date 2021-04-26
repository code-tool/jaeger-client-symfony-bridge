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

You can configure custom name generators based on regular expression pattern, which will be evaluated for operation name.

Configuration for this feature looks like key-value list, where key - regexp pattern, value - custom name generator DI service id (see details below).

Name generator should implement an 'TODO' interface.
As a custom name generator you can specify a full DI service id, or just the suffix if your name generator service is named as `jaeger.name.generator.*`.
Keys are considered body of the regular expression pattern, do not put any modifiers (e.g. `/i`, `/g`) or slashes; `route` of the request or `name` of the command should match to use alternative generator.
Expressions are checked top to bottom, if no match is found, default generator will be used

Example bundle config with name generation feature:

```yaml
# config/jaeger.yaml
jaeger:
  name_generator:
    max_length: 32
    command:  
      '^app:report:.+': 'my_service_generator_alias'
      .* : 'controller'
    request:
      'user_routes_\w+': 'my_service_generator_alias'
```
