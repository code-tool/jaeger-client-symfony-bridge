<?php
namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BackgroundStartListener implements EventSubscriberInterface
{
    private $handler;

    public function __construct(BackgroundSpanHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::TERMINATE => ['onTerminate', 4096],];
    }

    public function onTerminate(PostResponseEvent $event)
    {
        $this->handler->start($event->getRequest());

        return $this;
    }
}
