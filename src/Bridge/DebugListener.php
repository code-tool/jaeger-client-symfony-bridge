<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Debug\Extractor\DebugExtractorInterface;
use Jaeger\Tracer\DebuggableInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DebugListener implements EventSubscriberInterface
{
    private $debuggable;

    private $extractor;

    /**
     * DebugListener constructor.
     *
     * @param DebuggableInterface     $debuggable
     * @param DebugExtractorInterface $extractor
     */
    public function __construct(DebuggableInterface $debuggable, DebugExtractorInterface $extractor)
    {
        $this->debuggable = $debuggable;
        $this->extractor = $extractor;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 8192],
            KernelEvents::REQUEST => ['onRequest', 8192],
            ConsoleEvents::TERMINATE => ['onTerminate'],
            KernelEvents::TERMINATE => ['onTerminate'],
        ];
    }

    /**
     * @return DebugListener
     */
    public function onTerminate()
    {
        $this->debuggable->disable();

        return $this;
    }

    /**
     * @return DebugListener
     */
    public function onCommand()
    {
        if ('' === ($debugId = $this->extractor->getDebug())) {
            return $this;
        }
        $this->debuggable->enable($debugId);

        return $this;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return DebugListener
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (false === $event->isMasterRequest()) {
            return $this;
        }

        if ('' === ($debugId = $this->extractor->getDebug())) {
            return $this;
        }
        $this->debuggable->enable($debugId);

        return $this;
    }
}
