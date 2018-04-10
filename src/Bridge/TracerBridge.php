<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Tracer\FlushableInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class TracerBridge implements EventSubscriberInterface
{
    private $tracer;

    public function __construct(FlushableInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::TERMINATE => ['onTerminate', -65536],
            KernelEvents::TERMINATE => ['onTerminate', -65536],
        ];
    }

    public function onTerminate()
    {
        $this->tracer->flush();
    }
}
