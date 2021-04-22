<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class GlobalSpanListener implements EventSubscriberInterface
{
    private GlobalSpanHandler $handler;

    public function __construct(GlobalSpanHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 30],
            TerminateEvent::class => ['onTerminate', 4096],
        ];
    }

    public function onTerminate(): GlobalSpanListener
    {
        $this->handler->finish();

        return $this;
    }

    public function onRequest(RequestEvent $event): GlobalSpanListener
    {
        if (false === $event->isMasterRequest()) {
            return $this;
        }
        $this->handler->start($event->getRequest());

        return $this;
    }
}
