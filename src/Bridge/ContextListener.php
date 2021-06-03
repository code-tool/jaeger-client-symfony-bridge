<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Context\Extractor\ContextExtractorInterface;
use Jaeger\Tracer\InjectableInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ContextListener implements EventSubscriberInterface
{
    private $injectable;

    private $extractor;

    public function __construct(
        InjectableInterface $injectable,
        ContextExtractorInterface $extractor
    ) {
        $this->injectable = $injectable;
        $this->extractor = $extractor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => ['onCommand', 8192],
            RequestEvent::class => ['onRequest', 8192],
        ];
    }

    public function onCommand(): void
    {
        $this->inject();
    }

    public function inject(): void
    {
        if (null === ($context = $this->extractor->extract())) {
            return;
        }
        $this->injectable->assign($context);
    }

    public function onRequest(RequestEvent $event): void
    {
        if (false === $this->isMainRequestEvent($event)) {
            return;
        }
        $this->inject();
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
