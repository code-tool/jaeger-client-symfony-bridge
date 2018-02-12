<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Debug\Extractor\DebugExtractorInterface;
use Jaeger\Tracer\DebuggableInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
            ConsoleEvents::COMMAND => ['onStart', 8192],
            KernelEvents::REQUEST => ['onStart', 8192],
            ConsoleEvents::TERMINATE => ['onTerminate'],
            KernelEvents::TERMINATE => ['onTerminate'],
        ];
    }

    public function onTerminate()
    {
        $this->debuggable->disable();

        return $this;
    }

    public function onStart()
    {
        if ('' === ($debugId = $this->extractor->getDebug())) {
            return $this;
        }
        $this->debuggable->enable($debugId);

        return $this;
    }
}
