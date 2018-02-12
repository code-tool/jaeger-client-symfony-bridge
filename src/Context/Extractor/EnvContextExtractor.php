<?php
declare(strict_types=1);

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

    public function __construct(CodecRegistry $registry, string $format, string $envName)
    {
        $this->registry = $registry;
        $this->format = $format;
        $this->envName = $envName;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 16384],
            ConsoleEvents::TERMINATE => ['onTerminate'],
        ];
    }

    public function onTerminate()
    {
        $this->context = null;

        return $this;
    }

    public function extract(): ?SpanContext
    {
        return $this->context;
    }

    public function onCommand()
    {
        if (null === ($data = $_ENV[$this->envName] ?? null)) {
            return $this;
        }

        if (null === ($context = $this->registry[$this->format]->decode($data))) {
            return $this;
        }
        $this->context = $context;

        return $this;
    }
}
