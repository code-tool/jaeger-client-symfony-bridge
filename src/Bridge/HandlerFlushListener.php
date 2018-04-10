<?php
namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class HandlerFlushListener implements EventSubscriberInterface
{
    private $backgroundHandler;

    private $globalHandler;

    public function __construct(BackgroundSpanHandler $backgroundHandler, GlobalSpanHandler $globalHandler)
    {
        $this->backgroundHandler = $backgroundHandler;
        $this->globalHandler = $globalHandler;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::TERMINATE => ['onTerminate', -16384],];
    }

    public function onTerminate()
    {
        $this->backgroundHandler->flush();
        $this->globalHandler->flush();

        return $this;
    }
}
