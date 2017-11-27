<?php
declare(strict_types=1);

namespace CodeTool\Opentracing\Symfony\Bridge;

use CodeTool\OpenTracing\Tracer\FlushableInterface;
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
            ConsoleEvents::TERMINATE => [['onTerminate']],
            KernelEvents::FINISH_REQUEST => [['onFinishRequest']],
        ];
    }

    public function onFinishRequest()
    {
        $this->tracer->flush();
    }
}
