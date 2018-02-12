<?php
namespace Jaeger\Symfony\Debug\Extractor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CookieDebugExtractor implements DebugExtractorInterface, EventSubscriberInterface
{
    private $debugId = '';

    private $cookieName;

    /**
     * CookieDebugExtractor constructor.
     *
     * @param string $cookieName
     */
    public function __construct($cookieName)
    {
        $this->cookieName = (string)$cookieName;
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

    /**
     * @return string
     */
    public function getDebug()
    {
        return (string)$this->debugId;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 8192],
            KernelEvents::TERMINATE => ['onTerminate'],
        ];
    }


}
