<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Debug\Extractor\DebugExtractorInterface;
use Jaeger\Tracer\DebuggableInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class DebugListener implements EventSubscriberInterface
{
    private $debuggable;

    private $extractor;

    public function __construct(DebuggableInterface $debuggable, DebugExtractorInterface $extractor)
    {
        $this->debuggable = $debuggable;
        $this->extractor = $extractor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => ['onCommand', 8192],
            RequestEvent::class => ['onRequest', 8192],
            ConsoleTerminateEvent::class => ['onTerminate'],
            TerminateEvent::class => ['onTerminate'],
        ];
    }

    public function onTerminate(): void
    {
        $this->debuggable->disable();
    }

    public function onCommand(): void
    {
        if ('' === ($debugId = $this->extractor->getDebug())) {
            return;
        }
        $this->debuggable->enable($debugId);
    }

    public function onRequest(RequestEvent $event)
    {
        if (false === $this->isMainRequestEvent($event)) {
            return;
        }
        if ('' === ($debugId = $this->extractor->getDebug())) {
            return;
        }
        $this->debuggable->enable($debugId);
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
