<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Context\Extractor\ContextExtractorInterface;
use Jaeger\Tracer\InjectableInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 8192],
            KernelEvents::REQUEST => ['onRequest', 8192],
        ];
    }

    /**
     * @return ContextListener
     */
    public function onCommand()
    {
        return $this->inject();
    }

    /**
     * @return ContextListener
     */
    public function inject()
    {
        if (null === ($context = $this->extractor->extract())) {
            return $this;
        }
        $this->injectable->assign($context);

        return $this;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return ContextListener
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (false === $event->isMasterRequest()) {
            return $this;
        }

        return $this->inject();
    }
}
