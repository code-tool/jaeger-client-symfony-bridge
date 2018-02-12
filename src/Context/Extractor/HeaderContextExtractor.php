<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Span\Context\SpanContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HeaderContextExtractor implements ContextExtractorInterface, EventSubscriberInterface
{
    /**
     * @var CodecInterface[]
     */
    private $registry;

    private $format;

    private $headerName;

    private $context;

    public function __construct(CodecRegistry $registry, string $format, string $headerName)
    {
        $this->registry = $registry;
        $this->format = $format;
        $this->headerName = $headerName;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 16384],
            KernelEvents::TERMINATE => ['onTerminate'],
        ];
    }

    public function extract(): ?SpanContext
    {
        return $this->context;
    }

    public function onTerminate(PostResponseEvent $event)
    {
        if (false === $event->isMasterRequest()) {
            return $this;
        }
        $this->context = null;

        return $this;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (false === $event->isMasterRequest()) {
            return $this;
        }
        $request = $event->getRequest();
        if ($request->headers->has($this->headerName)
            && ($context = $this->registry[$this->format]->decode($request->headers->get($this->headerName)))) {
            $this->context = $context;

            return $this;
        }

        return $this;
    }
}
