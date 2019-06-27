<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class BackgroundStartListener implements EventSubscriberInterface
{
    private $handler;

    public function __construct(BackgroundSpanHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents(): array
    {
        return [TerminateEvent::class => ['onTerminate', 16384],];
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $this->handler->start($event->getRequest());
    }
}
