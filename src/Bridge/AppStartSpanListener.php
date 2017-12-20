<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class AppStartSpanListener implements EventSubscriberInterface
{
    private $tracer;

    public function __construct(TracerInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    public function getStartTime(Request $request)
    {
        return (int)($request->server->get('REQUEST_TIME_FLOAT', microtime(true)) * 1000000);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->tracer->finish($this->tracer->start('app.start')->start($this->getStartTime($request)));
        }

        return $this;
    }
}
