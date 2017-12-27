<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Tracer\InjectableInterface;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ContextInjector implements EventSubscriberInterface
{
    private $injectable;

    private $tracer;

    /**
     * @var CodecInterface[]
     */
    private $registry;

    private $format;

    private $envName;

    private $headerName;

    public function __construct(
        InjectableInterface $injectable,
        TracerInterface $tracer,
        CodecRegistry $registry,
        string $format,
        string $envName,
        string $headerName
    ) {
        $this->injectable = $injectable;
        $this->tracer = $tracer;
        $this->registry = $registry;
        $this->format = $format;
        $this->envName = $envName;
        $this->headerName = $headerName;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 1024],
            KernelEvents::REQUEST => ['onRequest', 1024],
        ];
    }

    public function onCommand()
    {
        if (($data = $_ENV[$this->envName] ?? null)
            && $context = $this->registry[$this->format]->decode($data)) {
            $this->injectable->assign($context);
        }


        return $this;
    }

    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()
            && $request->headers->has($this->headerName)
            && ($context = $this->registry[$this->format]->decode($request->headers->get($this->headerName)))) {
            $this->injectable->assign($context);
        }

        return $this;
    }
}
