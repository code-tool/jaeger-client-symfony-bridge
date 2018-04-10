<?php
namespace Jaeger\Symfony\Name\Generator;

class NameGeneratorChain implements NameGeneratorInterface
{
    private $queue;

    public function __construct(\SplPriorityQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param NameGeneratorInterface $extractor
     * @param int                    $priority
     *
     * @return NameGeneratorChain
     */
    public function add(NameGeneratorInterface $extractor, $priority = 0)
    {
        $this->queue->insert($extractor, (int)$priority);

        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $queue = clone $this->queue;
        while (false === $queue->isEmpty()) {
            if ('' !== ($debugId = $queue->extract()->generate())) {
                return $debugId;
            }
        }

        return '';
    }
}
