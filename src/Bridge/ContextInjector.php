<?php
namespace Jaeger\Symfony\Bridge;

use Jaeger\Symfony\Context\Extractor\ContextExtractorInterface;
use Jaeger\Tracer\InjectableInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ContextInjector implements EventSubscriberInterface
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
            ConsoleEvents::COMMAND => ['onCommand', 1024],
            KernelEvents::REQUEST => ['onRequest', 1024],
        ];
    }

    public function inject(): ContextInjector
    {
        if (null === ($context = $this->extractor->extract())) {
            return $this;
        }
        $this->injectable->assign($context);

        return $this;
    }

    public function onCommand()
    {
        return $this->inject();
    }

    public function onRequest()
    {
        return $this->inject();
    }
}
