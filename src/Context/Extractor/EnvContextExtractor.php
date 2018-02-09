<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Span\Context\SpanContext;
use Symfony\Component\Console\ConsoleEvents;

class EnvContextExtractor implements ContextExtractorInterface
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

    public function extract(): ?SpanContext
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
