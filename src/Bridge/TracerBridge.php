<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Tracer\FlushableInterface;
use Jaeger\Tracer\ResettableInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class TracerBridge implements EventSubscriberInterface
{
    private $tracer;

    public function __construct(FlushableInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleTerminateEvent::class => ['onTerminate', -65536],
            TerminateEvent::class => ['onTerminate', -65536],
        ];
    }

    public function onTerminate(): void
    {
        $this->tracer->flush();
        if ($this->tracer instanceof ResettableInterface) {
            $this->tracer->reset();
        }
    }
}
