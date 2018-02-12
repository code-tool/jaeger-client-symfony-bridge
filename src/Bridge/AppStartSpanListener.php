<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Symfony\Tag\TimeMicroTag;
use Jaeger\Symfony\Tag\TimeSourceTag;
use Jaeger\Symfony\Tag\TimeValueTag;
use Jaeger\Tag\SpanKindServerTag;
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
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return $this;
        }
        $source = $request->server->has('REQUEST_TIME_FLOAT') ? 'header' : 'microtime';
        $value = $request->server->get('REQUEST_TIME_FLOAT', microtime(true));
        $startTime = (int)($value * 1000000);
        $this->tracer->finish(
            $this->tracer->start('symfony.start')
                ->addTag(new SpanKindServerTag())
                ->addTag(new SymfonyComponentTag())
                ->addTag(new SymfonyVersionTag())
                ->addTag(new TimeSourceTag($source))
                ->addTag(new TimeValueTag($value))
                ->addTag(new TimeMicroTag($startTime))
                ->start($startTime)
        );

        return $this;
    }
}
