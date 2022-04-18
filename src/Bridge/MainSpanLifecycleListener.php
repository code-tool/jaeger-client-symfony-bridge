<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class MainSpanLifecycleListener implements EventSubscriberInterface
{
    private MainSpanHandler $handler;

    public function __construct(MainSpanHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 1024],
            TerminateEvent::class => ['onTerminate', 4096],
        ];
    }

    public function onTerminate(): MainSpanLifecycleListener
    {
        $this->handler->finish();

        return $this;
    }

    public function onRequest(RequestEvent $event): MainSpanLifecycleListener
    {
        if (false === $this->isMainRequestEvent($event)) {
            return $this;
        }
        $this->handler->start($event->getRequest());

        return $this;
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
}
