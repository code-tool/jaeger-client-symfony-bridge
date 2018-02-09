<?php

namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Span\Context\SpanContext;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EnvContextExtractor implements ContextExtractorInterface, EventSubscriberInterface
{
    /**
     * @var CodecInterface[]
     */
    private $registry;

    private $format;

    private $envName;

    private $context;

    /**
     * EnvContextExtractor constructor.
     *
     * @param CodecRegistry $registry
     * @param string        $format
     * @param string        $envName
     */
    public function __construct(CodecRegistry $registry, $format, $envName)
    {
        $this->registry = $registry;
        $this->format = (string)$format;
        $this->envName = (string)$envName;
    }

    /**
     * @return SpanContext|null
     */
    public function extract()
    {
        return $this->context;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 2048],
        ];
    }

    public function onCommand()
    {
        if (false === array_key_exists($this->envName, $_ENV)) {
            return $this;
        }

        if (null === ($data = $_ENV[$this->envName])) {
            return $this;
        }

        if (null === ($context = $this->registry[$this->format]->decode($data))) {
            return $this;
        }
        $this->context = $context;

        return $this;
    }
}
