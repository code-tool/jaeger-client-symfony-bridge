<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class ControllerNameGenerator implements NameGeneratorInterface, EventSubscriberInterface
{
    private string $controller = '';

    public static function getSubscribedEvents(): array
    {
        return [
            // Subscribe after route was resolved and request attributes were set
            RequestEvent::class => ['onRequest', 31],
            TerminateEvent::class => ['onTerminate', -16384],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->controller = (string)$event->getRequest()->attributes->get('_controller', '');
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
