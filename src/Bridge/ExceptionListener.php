<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Span\SpanAwareInterface;
use Jaeger\Tag\ErrorTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListener implements EventSubscriberInterface
{
    private $tracer;
    private $spanManager;
    private $globalSpanHandler;
    private $exceptionExist;

    public function __construct(
        TracerInterface $tracer,
        SpanAwareInterface $spanManager,
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
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $span = $this->spanManager->getSpan();

        if (null === $span) {
            return;
        }

        $span->addTag(new ErrorTag());
        $this->exceptionExist = true;
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($this->exceptionExist) {
            $this->spanManager->getSpan()->addTag(new ErrorTag());
        }
    }
}
