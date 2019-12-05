<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Log\ErrorLog;
use Jaeger\Span\SpanAwareInterface;
use Jaeger\Tag\ErrorTag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener implements EventSubscriberInterface
{
    private $spanManager;

    public function __construct(SpanAwareInterface $spanManager)
    {
        $this->spanManager = $spanManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $span = $this->spanManager->getSpan();

        if (null === $span) {
            return;
        }

        $exception = $event->getThrowable();

        $span
            ->addTag(new ErrorTag())
            ->addLog(new ErrorLog($exception->getMessage(), $exception->getTraceAsString()))
        ;
    }
}
