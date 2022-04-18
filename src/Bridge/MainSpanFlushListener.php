<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class MainSpanFlushListener implements EventSubscriberInterface
{
    private $backgroundHandler;

    private $globalHandler;

    public function __construct(BackgroundSpanHandler $backgroundHandler, MainSpanHandler $globalHandler)
    {
        $this->backgroundHandler = $backgroundHandler;
        $this->globalHandler = $globalHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [TerminateEvent::class => ['onTerminate', -16384]];
    }

    public function onTerminate()
    {
        $this->backgroundHandler->flush();
        $this->globalHandler->flush();

        return $this;
    }
}
