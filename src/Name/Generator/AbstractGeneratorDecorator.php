<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

abstract class AbstractGeneratorDecorator implements NameGeneratorInterface
{
    private NameGeneratorInterface $generator;

    public function __construct(NameGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function generate(): string
    {
        return $this->generator->generate();
    }
}
