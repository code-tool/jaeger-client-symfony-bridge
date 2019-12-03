<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Span\SpanManagerInterface;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListener implements EventSubscriberInterface
{
    private $tracer;
    private $spanManager;
    private $globalSpanHandler;
    private $exceptionExist;

    public function __construct(
        TracerInterface $tracer,
        SpanManagerInterface $spanManager,
        GlobalSpanHandler $globalSpanHandler
    ) {
        $this->tracer = $tracer;
        $this->spanManager = $spanManager;
        $this->globalSpanHandler = $globalSpanHandler;
        $this->exceptionExist = false;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => ['onKernelException', 0],
            RequestEvent::class => ['onRequest', 28],
            TerminateEvent::class => ['onTerminate', 4097],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $span = $this->spanManager->getSpan();

        if (null !== $span) {
            $span->addTag(new ErrorTag());
            $this->exceptionExist = true;
        }
    }

    public function onRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            return;
        }

        if ($this->exceptionExist) {
            $span = $this->spanManager->getSpan();
            $span->addTag(new ErrorTag());
        }
    }

    public function onTerminate(): void
    {
        if ($this->exceptionExist) {
            $this->globalSpanHandler->addTag(new ErrorTag());
        }
    }
}
