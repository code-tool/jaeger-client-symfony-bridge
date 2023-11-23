<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Span\Context\SpanContext;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EnvContextExtractor implements ContextExtractorInterface, EventSubscriberInterface
{
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

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => ['onCommand', 16384],
            ConsoleTerminateEvent::class => ['onTerminate'],
        ];
    }

    public function onTerminate(): void
    {
        $this->context = null;
    }

    public function extract(): ?SpanContext
    {
        return $this->context;
    }

    public function onCommand(): void
    {
        if (null === ($data = $_ENV[$this->envName] ?? null)) {
            return;
        }
        if (null === ($context = $this->registry[$this->format]->decode($data))) {
            return;
        }
        $this->context = $context;
    }
}
