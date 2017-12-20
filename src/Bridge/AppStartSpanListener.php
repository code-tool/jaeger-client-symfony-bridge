<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Tag\DoubleTag;
use Jaeger\Tag\LongTag;
use Jaeger\Tag\StringTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', -1],
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $source = $request->server->has('REQUEST_TIME_FLOAT') ? 'header' : 'microtime';
            $value = $request->server->get('REQUEST_TIME_FLOAT', microtime(true));
            $startTime = (int)($value * 1000000);
            $this->tracer->finish(
                $this->tracer->start('app.start')
                    ->addTag(new StringTag('time.source', $source))
                    ->addTag(new DoubleTag('time.value', $value))
                    ->addTag(new LongTag('time.micro', $startTime))
                    ->start($startTime)
            );
        }

        return $this;
    }
}
