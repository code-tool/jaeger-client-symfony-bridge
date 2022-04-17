<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Tag\SymfonyComponentTag;
use Jaeger\Symfony\Tag\SymfonyVersionTag;
use Jaeger\Tag\SpanKindServerTag;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class AppStartSpanListener implements EventSubscriberInterface
{
    private $tracer;

    public function __construct(TracerInterface $tracer)
    {
        $this->tracer = $tracer;
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::class => ['onRequest', -1023],];
    }

    public function onRequest(RequestEvent $event)
    {
        if (false === $this->isMainRequestEvent($event)) {
            return $this;
        }

        $this->tracer
            ->start('symfony.start')
            ->addTag(new SpanKindServerTag())
            ->addTag(new SymfonyComponentTag())
            ->addTag(new SymfonyVersionTag())
            ->start((int)(1000000 * $event->getRequest()->server->get('REQUEST_TIME_FLOAT', microtime(true))))
            ->finish();

        return $this;
    }

    /**
     * Use non-deprecated check method if availble
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    private function isMainRequestEvent(KernelEvent $event): bool
    {
        if (\method_exists($event, 'isMainRequest')) {
            return $event->isMainRequest();
        }

        return $event->isMasterRequest();
    }
}
