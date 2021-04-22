<?php

namespace Jaeger\Symfony\Name\Generator;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class ControllerNameGenerator implements NameGeneratorInterface, EventSubscriberInterface
{
    private string $controller;

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => ['onRequest', 30], TerminateEvent::class => ['onTerminate', -16384],];
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->controller = $event->getRequest()->attributes->get('_controller', '');
    }

    public function onTerminate(): void
    {
        $this->controller = '';
    }

    public function generate(): string
    {
        return $this->controller;
    }
}
