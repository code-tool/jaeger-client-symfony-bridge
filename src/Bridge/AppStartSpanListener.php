<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AppStartSpanListener implements EventSubscriberInterface
{
    private $tracer;

    public function __construct(TracerInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => ['onRequest', -1],];
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return $this;
        }
        $this->tracer
            ->start('symfony.start')
            ->addTag(new SpanKindServerTag())
            ->addTag(new SymfonyComponentTag())
            ->addTag(new SymfonyVersionTag())
            ->start(1000000 * $request->server->get('REQUEST_TIME_FLOAT', microtime(true)))
            ->finish();

        return $this;
    }
}
