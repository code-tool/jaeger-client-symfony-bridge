<?php
declare(strict_types=1);

namespace Jaeger\Symfony\Bridge;

use Ds\Stack;
use Jaeger\Codec\CodecInterface;
use Jaeger\Codec\CodecRegistry;
use Jaeger\Http\HttpMethodTag;
use Jaeger\Http\HttpUriTag;
use Jaeger\Tracer\InjectableInterface;
use Jaeger\Tracer\TracerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ContextInjector implements EventSubscriberInterface
{
    private $spans;

    private $injectable;

    private $tracer;

    /**
     * @var CodecInterface[]
     */
    private $registry;

    private $requestStack;

    private $format;

    private $envName;

    private $headerName;

    public function __construct(
        Stack $stack,
        InjectableInterface $injectable,
        TracerInterface $tracer,
        CodecRegistry $registry,
        RequestStack $requestStack,
        string $format,
        string $envName,
        string $headerName
    ) {
        $this->spans = $stack;
        $this->injectable = $injectable;
        $this->tracer = $tracer;
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->format = $format;
        $this->envName = $envName;
        $this->headerName = $headerName;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => [['onCommand']],
            ConsoleEvents::TERMINATE => [['onFinish']],
            KernelEvents::REQUEST => [['onRequest']],
            KernelEvents::FINISH_REQUEST => [['onFinish']],
        ];
    }

    public function getOperationName(GetResponseEvent $event)
    {
        switch ($event->getRequestType()) {
            case HttpKernelInterface::MASTER_REQUEST:
                return 'symfony.request';
            default:
                return 'symfony.subrequest';
        }
    }

    public function onFinish()
    {
        while (0 !== $this->spans->count()) {
            $this->tracer->finish($this->spans->pop());
        }

        return $this;
    }

    public function onCommand()
    {
        if (null === ($data = $_ENV[$this->envName] ?? null)) {
            return $this;
        }
        if (null === ($context = $this->registry[$this->format]->decode($data))) {
            return $this;
        }
        $this->injectable->assign($context);

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

        $this->spans->push(
            $this->tracer
                ->start(
                    $this->getOperationName($event),
                    [
                        new HttpMethodTag($request->getMethod()),
                        new HttpUriTag($request->getUri()),
                    ]
                )
        );

        return $this;
    }
}
