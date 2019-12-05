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
