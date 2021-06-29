<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

class ShortenGeneratorDecorator extends AbstractGeneratorDecorator
{
    private int $maxLength;

    public function __construct(NameGeneratorInterface $generator, int $maxLength)
    {
        $this->maxLength = $maxLength;
        parent::__construct($generator);
    }

    public function shorten(string $name)
    {
        if ($this->maxLength >= \strlen($name)) {
            return $name;
        }

        return \substr($name, 0, $this->maxLength);
    }

    public function generate(): string
    {
        return $this->shorten(parent::generate());
    }
}
