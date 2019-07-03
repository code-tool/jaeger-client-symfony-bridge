<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
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
        $this->tracer->finish(
            $this->tracer->start('symfony.start')
                ->addTag(new SpanKindServerTag())
                ->addTag(new SymfonyComponentTag())
                ->addTag(new SymfonyVersionTag())
                ->start((int)(1000000 * $request->server->get('REQUEST_TIME_FLOAT', microtime(true))))
        );

        return $this;
    }
}
