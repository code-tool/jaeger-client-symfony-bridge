<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class CookieDebugExtractor implements DebugExtractorInterface, EventSubscriberInterface
{
    private $debugId = '';

    private $cookieName;

    public function __construct(string $cookieName)
    {
        $this->cookieName = $cookieName;
    }

    public function onRequest(RequestEvent $event): void
    {
        if (false === $event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (false === $request->cookies->has($this->cookieName)) {
            return;
        }
        $this->debugId = (string)$request->cookies->get($this->cookieName, '');
    }

    public function onTerminate(TerminateEvent $event): void
    {
        if (false === $event->isMasterRequest()) {
            return;
        }
        $this->debugId = '';
    }

    public function getDebug(): string
    {
        return $this->debugId;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 16384],
            TerminateEvent::class => ['onTerminate'],
        ];
    }
}
