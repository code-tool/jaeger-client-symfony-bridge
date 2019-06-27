<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GlobalSpanListener implements EventSubscriberInterface
{
    private $handler;

    public function __construct(GlobalSpanHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => ['onRequest', 30],
            TerminateEvent::class => ['onTerminate', 4096],
        ];
    }

    public function onTerminate()
    {
        $this->handler->finish();

        return $this;
    }

    public function onRequest(RequestEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return $this;
        }
        $this->handler->start($event->getRequest());

        return $this;
    }
}
