<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

class NameGeneratorChain implements NameGeneratorInterface
{
    private \SplPriorityQueue $queue;

    public function __construct(\SplPriorityQueue $queue)
    {
        $this->queue = $queue;
    }

    public function add(NameGeneratorInterface $extractor, int $priority = 0): NameGeneratorChain
    {
        $this->queue->insert($extractor, $priority);

        return $this;
    }

    public function generate(): string
    {
        $queue = clone $this->queue;
        while (false === $queue->isEmpty()) {
            if ('' !== ($name = $queue->extract()->generate())) {
                return $name;
            }
        }

        return 'route.unknown';
    }
}
