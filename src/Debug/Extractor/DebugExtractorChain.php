<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

class DebugExtractorChain implements DebugExtractorInterface
{
    private $queue;

    public function __construct(\SplPriorityQueue $queue)
    {
        $this->queue = $queue;
    }

    public function add(DebugExtractorInterface $extractor, int $priority = 0): DebugExtractorChain
    {
        $this->queue->insert($extractor, $priority);

        return $this;
    }

    public function getDebug(): string
    {
        $queue = clone $this->queue;
        while (false === $queue->isEmpty()) {
            if ('' !== ($debugId = $queue->extract()->getDebug())) {
                return $debugId;
            }
        }

        return '';
    }
}
