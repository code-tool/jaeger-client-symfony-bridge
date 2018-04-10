<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

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
            KernelEvents::REQUEST => ['onRequest', 30],
            KernelEvents::TERMINATE => ['onTerminate', 4096],
        ];
    }

    public function onTerminate()
    {
        $this->handler->finish();

        return $this;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return $this;
        }
        $this->handler->start($event->getRequest());

        return $this;
    }
}
