<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Debug\Extractor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CookieDebugExtractor implements DebugExtractorInterface, EventSubscriberInterface
{
    private $debugId = '';

    private $cookieName;

    public function __construct(string $cookieName)
    {
        $this->cookieName = $cookieName;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return $this;
        }

        $request = $event->getRequest();
        if (false === $request->cookies->has($this->cookieName)) {
            return $this;
        }

        $this->debugId = (string)$request->cookies->get($this->cookieName, '');

        return $this;
    }

    public function onTerminate()
    {
        $this->debugId = '';

        return $this;
    }

    public function getDebug(): string
    {
        return $this->debugId;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 16384],
            KernelEvents::TERMINATE => ['onTerminate'],
        ];
    }


}
