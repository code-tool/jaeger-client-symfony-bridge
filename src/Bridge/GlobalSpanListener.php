<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

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
