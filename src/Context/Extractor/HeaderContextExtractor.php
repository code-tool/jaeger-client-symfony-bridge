<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Context\Extractor;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Span\Context\SpanContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Contracts\Service\ResetInterface;

class HeaderContextExtractor implements ContextExtractorInterface, EventSubscriberInterface, ResetInterface
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

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 16384],
            TerminateEvent::class => ['onTerminate'],
        ];
    }

    public function extract(): ?SpanContext
    {
        return $this->context;
    }

    public function onTerminate(TerminateEvent $event): void
    {
        if (false === $this->isMainRequestEvent($event)) {
            return;
        }

        $this->reset();
    }

    public function onRequest(RequestEvent $event): void
    {
        if (false === $this->isMainRequestEvent($event)) {
            return;
        }
        $request = $event->getRequest();
        if ($request->headers->has($this->headerName)
            && ($context = $this->registry[$this->format]->decode($request->headers->get($this->headerName)))) {
            $this->context = $context;
        }
    }

    /**
     * Use non-deprecated check method if availble
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    private function isMainRequestEvent(KernelEvent $event): bool
    {
        if (\method_exists($event, 'isMainRequest')) {
            return $event->isMainRequest();
        }

        return $event->isMasterRequest();
    }

    public function reset(): void
    {
        $this->context = null;
    }
}
