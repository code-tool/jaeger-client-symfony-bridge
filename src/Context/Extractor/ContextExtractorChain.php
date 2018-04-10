<?php
namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Span\Context\SpanContext;

class ContextExtractorChain implements ContextExtractorInterface
{
    private $queue;

    public function __construct(\SplPriorityQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param ContextExtractorInterface $extractor
     * @param int                       $priority
     *
     * @return ContextExtractorChain
     */
    public function add(ContextExtractorInterface $extractor, $priority = 0)
    {
        $this->queue->insert($extractor, (int)$priority);

        return $this;
    }

    /**
     * @return SpanContext|null
     */
    public function extract()
    {
        $queue = clone $this->queue;
        while (false === $queue->isEmpty()) {
            if (null !== ($context = $queue->extract()->extract())) {
                return $context;
            }
        }

        return null;
    }
}
