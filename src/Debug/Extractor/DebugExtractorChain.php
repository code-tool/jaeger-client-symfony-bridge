<?php
namespace Jaeger\Symfony\Debug\Extractor;

class DebugExtractorChain implements DebugExtractorInterface
{
    private $queue;

    /**
     * DebugExtractorChain constructor.
     *
     * @param \SplPriorityQueue $queue
     */
    public function __construct(\SplPriorityQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param DebugExtractorInterface $extractor
     * @param int                     $priority
     *
     * @return DebugExtractorChain
     */
    public function add(DebugExtractorInterface $extractor, $priority = 0)
    {
        $this->queue->insert($extractor, (int)$priority);

        return $this;
    }

    /**
     * @return string
     */
    public function getDebug()
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
