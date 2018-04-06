<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Symfony\Name\Generator\NameGeneratorInterface;
use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class GlobalSpanListener implements EventSubscriberInterface
{
    private $span;

    private $nameGenerator;

    private $tracer;

    public function __construct(NameGeneratorInterface $nameGenerator, TracerInterface $tracer)
    {
        $this->nameGenerator = $nameGenerator;
        $this->tracer = $tracer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 30],
            KernelEvents::TERMINATE => ['onTerminate', 4096],
        ];
    }

    public function onTerminate()
    {
        if (null === $this->span) {
            return $this;
        }
        $this->tracer->finish($this->span);

        return $this;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return $this;
        }

        $request = $event->getRequest();
        $this->span = $this->tracer->start(
            $this->nameGenerator->generate(),
            [
                new HttpMethodTag($request->getMethod()),
                new HttpUriTag($request->getRequestUri()),
                new SpanKindServerTag(),
                new SymfonyComponentTag(),
                new SymfonyVersionTag()
            ]
        )->start((int)(1000000 * $request->server->get('REQUEST_TIME_FLOAT', microtime(true))));

        return $this;
    }
}
