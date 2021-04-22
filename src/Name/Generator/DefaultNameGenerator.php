<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Name\Generator;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class DefaultNameGenerator implements NameGeneratorInterface, EventSubscriberInterface
{
    private string $name = '';

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 30],
            ConsoleCommandEvent::class => ['onCommand', 30],
            TerminateEvent::class => ['onTerminate', -16384],
            ConsoleTerminateEvent::class => ['onTerminate'],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event): void
    {
        if (null === $event->getCommand()) {
            return;
        }

        $this->name = (string)$event->getCommand()->getName();
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (null !== ($fragment = $request->attributes->get('is_fragment'))) {
            $this->name = ($controller = $request->attributes->get('_controller', null))
                ? sprintf('fragment.%s', $controller)
                : 'fragment';
            return;
        }
        $this->name = $request->attributes->get('_route', $request->getRequestUri());
    }

    public function onTerminate(): void
    {
        $this->name = '';
    }

    public function generate(): string
    {
        return $this->name;
    }
}
